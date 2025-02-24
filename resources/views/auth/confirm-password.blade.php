@extends('layouts.crm.crm')
@section('content')
<div class="pc-container">
    <div class="pc-content">
        <div class="pb-0 mb-0 page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title h2">
                            <h4 class="mb-0">Transactions</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-auth-layout>
            <div title="Confirm Password">
                <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                </div>

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf
                    <div>
                        <x-input-label for="password" :value="__('Password')" />

                        <x-input-field id="password" class="block w-25 mt-1" type="password" name="password" required
                            autocomplete="current-password" />

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex justify-end mt-4">
                        <x-primary-button>
                            {{ __('Confirm') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </x-auth-layout>
    </div>
</div>
@endsection
