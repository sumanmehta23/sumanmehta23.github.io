<?php

namespace App\Support\PopupCampaigns;

use App\Models\Account;
use App\Models\RestrictIps;
use App\Models\TradeWithdrawals;
use App\Models\User;
use App\Models\WalletWithdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReviewPopupCampaign
{
    public function __construct(private array $settings)
    {
    }

    /**
     * Stable key for impressions, API validation, and admin metrics. Separate from Popup ID (DOM element id).
     */
    public function key(): string
    {
        $key = trim((string) ($this->settings['review_popup_campaign_key'] ?? ''));

        return $key !== '' ? $key : 'review_popup_v1';
    }

    public function isEnabled(): bool
    {
        return $this->settingOn($this->settings['review_popup_enabled'] ?? null, true);
    }

    public function shouldAutoOpen(): bool
    {
        return $this->settingOn($this->settings['review_popup_show_on_load'] ?? null, true);
    }

    public function delayMs(): int
    {
        return 3000;
    }

    public function targetPages(): Collection
    {
        $pagesRaw = (string) ($this->settings['review_popup_pages']);
        if (trim($pagesRaw) === '') {
            $pagesRaw = 'dashboard,liveAccounts';
        }

        return collect(preg_split('/[\r\n,;|]+/', $pagesRaw))
            ->map(function ($page) {
                $page = trim($page);
                $page = ltrim($page, '/');

                return $page;
            })
            ->filter()
            ->values();
    }

    public function matchesPage(Request $request): bool
    {
        return $this->matchesPageContext(
            trim($request->path(), '/'),
            optional($request->route())->getName()
        );
    }

    public function matchesPageContext(?string $currentPath, ?string $currentRouteName): bool
    {
        $currentPath = trim((string) $currentPath, '/');
        $currentRouteName = $currentRouteName !== null ? trim($currentRouteName) : null;

        return $this->targetPages()->isNotEmpty()
            && $this->targetPages()->contains(function ($page) use ($currentPath, $currentRouteName) {
                $pathMatch = Str::is($page, $currentPath)
                    || Str::lower($page) === Str::lower($currentPath);
                $routeMatch = !empty($currentRouteName)
                    && (Str::is($page, $currentRouteName)
                        || Str::lower($page) === Str::lower($currentRouteName));

                return $pathMatch || $routeMatch;
            });
    }

    public function canRender(Request $request): bool
    {
        return $request->user() !== null
            && $this->isEnabled()
            && $this->matchesPage($request);
    }

    public function isUserEligible(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $requireWithdrawal = $this->settingOn($this->settings['review_popup_require_withdrawal'] ?? null, true);
        $requireLiveAccountAge = $this->settingOn($this->settings['review_popup_require_live_account_age'] ?? null, true);
        $excludeBanned = $this->settingOn($this->settings['review_popup_exclude_banned'] ?? null, true);
        $excludeFlagged = $this->settingOn($this->settings['review_popup_exclude_flagged'] ?? null, true);
        $liveAccountMinDays = max(0, (int) ($this->settings['review_popup_live_account_min_days'] ?? 3));

        $hasCompletedFirstWithdrawal =
            TradeWithdrawals::where('user_id', $user->id)
            ->where('withdraw_type', 'Trade Withdrawal')
            ->where('status', 1)
            ->exists()
            || WalletWithdraw::where('user_id', $user->id)
            ->where('withdraw_type', 'Wallet Withdrawal')
            ->where('status', 1)
            ->exists();

        $hasFlaggedAccount = Account::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->where('sync_status', 'flagged')
            ->exists();

        $isBannedUser = RestrictIps::where('email', $user->email)
            ->whereNull('deleted_at')
            ->where('block_reason', 'General_Ban')
            ->exists();

        $hasLiveAccountOpenLongEnough = Account::where('user_id', $user->id)
            ->where('demo', 0)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($liveAccountMinDays) {
                $query->whereNull('created_at')
                    ->orWhere('created_at', '<=', now()->subDays($liveAccountMinDays));
            })
            ->exists();

        return (!$requireWithdrawal || $hasCompletedFirstWithdrawal)
            && (!$excludeFlagged || !$hasFlaggedAccount)
            && (!$excludeBanned || !$isBannedUser)
            && (!$requireLiveAccountAge || $hasLiveAccountOpenLongEnough);
    }

    private function settingOn($value, bool $default = true): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }
}
