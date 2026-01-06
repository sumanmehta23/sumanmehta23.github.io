<?php

namespace App\Http\Requests\Zapier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateZapierAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all requests (webhook from Zapier)
        return true;
    }

    /**
     * Handle a failed validation attempt (return JSON for API)
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:aspnetusers,email|max:255',
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'account_type' => 'nullable|string|in:Demo,Live,Standard', // Demo Standard or Live
            'group_code' => 'nullable|string|max:50', // MT5 group code if needed
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'first_name.string' => 'First name must be a string',
            'first_name.max' => 'First name cannot exceed 255 characters',
            'last_name.required' => 'Last name is required',
            'last_name.string' => 'Last name must be a string',
            'last_name.max' => 'Last name cannot exceed 255 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'This email is already registered',
            'email.max' => 'Email cannot exceed 255 characters',
            'phone.required' => 'Phone number is required',
            'phone.string' => 'Phone must be a string',
            'phone.max' => 'Phone cannot exceed 20 characters',
            'country.required' => 'Country is required',
            'country.string' => 'Country must be a string',
            'country.max' => 'Country cannot exceed 100 characters',
            'account_type.in' => 'Account type must be Demo, Live, or Standard',
            'group_code.max' => 'Group code cannot exceed 50 characters',
        ];
    }

    /**
     * Extract country code from phone number using comprehensive country code database
     * 
     * @param string $phone Full phone number with country code
     * @return array ['country_code' => '+91', 'phone_number' => '9812309999']
     */
    protected function extractCountryCode(string $phone): array
    {
        // Comprehensive list of country calling codes (most common ones)
        // Format: code => expected_phone_length (or array of lengths)
        $countryCodes = [
            // 1-digit codes
            '1' => [10, 11],  // US, Canada, Caribbean
            '7' => [10],      // Russia, Kazakhstan
            
            // 2-digit codes (most common)
            '20' => [9, 10],  // Egypt
            '27' => [9],      // South Africa
            '30' => [10],     // Greece
            '31' => [9],      // Netherlands
            '32' => [8, 9],   // Belgium
            '33' => [9],      // France
            '34' => [9],      // Spain
            '36' => [9],      // Hungary
            '39' => [9, 10],  // Italy
            '40' => [9],      // Romania
            '41' => [9],      // Switzerland
            '43' => [10, 11], // Austria
            '44' => [10],     // UK
            '45' => [8],      // Denmark
            '46' => [7, 9],   // Sweden
            '47' => [8],      // Norway
            '48' => [9],      // Poland
            '49' => [10, 11], // Germany
            '51' => [9],      // Peru
            '52' => [10],     // Mexico
            '53' => [8],      // Cuba
            '54' => [10],     // Argentina
            '55' => [10, 11], // Brazil
            '56' => [9],      // Chile
            '57' => [10],     // Colombia
            '58' => [10],     // Venezuela
            '60' => [9, 10],  // Malaysia
            '61' => [9],      // Australia
            '62' => [9, 10, 11], // Indonesia
            '63' => [10],     // Philippines
            '64' => [8, 9, 10], // New Zealand
            '65' => [8],      // Singapore
            '66' => [9],      // Thailand
            '81' => [10],     // Japan
            '82' => [9, 10],  // South Korea
            '84' => [9, 10],  // Vietnam
            '86' => [11],     // China
            '90' => [10],     // Turkey
            '91' => [10],     // India
            '92' => [10],     // Pakistan
            '93' => [9],      // Afghanistan
            '94' => [9],      // Sri Lanka
            '95' => [8, 9],   // Myanmar
            '98' => [10],     // Iran
            
            // 3-digit codes
            '212' => [9],     // Morocco
            '213' => [9],     // Algeria
            '216' => [8],     // Tunisia
            '218' => [9],     // Libya
            '220' => [7],     // Gambia
            '221' => [9],     // Senegal
            '222' => [8],     // Mauritania
            '223' => [8],     // Mali
            '224' => [9],     // Guinea
            '225' => [8],     // Ivory Coast
            '226' => [8],     // Burkina Faso
            '227' => [8],     // Niger
            '228' => [8],     // Togo
            '229' => [8],     // Benin
            '230' => [7, 8],  // Mauritius
            '231' => [7, 8],  // Liberia
            '232' => [8],     // Sierra Leone
            '233' => [9],     // Ghana
            '234' => [10],    // Nigeria
            '235' => [8],     // Chad
            '236' => [8],     // Central African Republic
            '237' => [9],     // Cameroon
            '238' => [7],     // Cape Verde
            '239' => [7],     // Sao Tome
            '240' => [9],     // Equatorial Guinea
            '241' => [7],     // Gabon
            '242' => [9],     // Republic of Congo
            '243' => [9],     // DR Congo
            '244' => [9],     // Angola
            '245' => [7],     // Guinea-Bissau
            '246' => [7],     // Diego Garcia
            '248' => [7],     // Seychelles
            '249' => [9],     // Sudan
            '250' => [9],     // Rwanda
            '251' => [9],     // Ethiopia
            '252' => [7, 8],  // Somalia
            '253' => [8],     // Djibouti
            '254' => [9, 10], // Kenya
            '255' => [9],     // Tanzania
            '256' => [9],     // Uganda
            '257' => [8],     // Burundi
            '258' => [9],     // Mozambique
            '260' => [9],     // Zambia
            '261' => [9],     // Madagascar
            '262' => [9],     // Reunion/Mayotte
            '263' => [9],     // Zimbabwe
            '264' => [9],     // Namibia
            '265' => [8, 9],  // Malawi
            '266' => [8],     // Lesotho
            '267' => [8],     // Botswana
            '268' => [8],     // Eswatini
            '269' => [7],     // Comoros
            '290' => [4],     // Saint Helena
            '291' => [7],     // Eritrea
            '297' => [7],     // Aruba
            '298' => [6],     // Faroe Islands
            '299' => [6],     // Greenland
            '350' => [8],     // Gibraltar
            '351' => [9],     // Portugal
            '352' => [9],     // Luxembourg
            '353' => [9],     // Ireland
            '354' => [7],     // Iceland
            '355' => [9],     // Albania
            '356' => [8],     // Malta
            '357' => [8],     // Cyprus
            '358' => [9, 10], // Finland
            '359' => [9],     // Bulgaria
            '370' => [8],     // Lithuania
            '371' => [8],     // Latvia
            '372' => [7, 8],  // Estonia
            '373' => [8],     // Moldova
            '374' => [8],     // Armenia
            '375' => [9],     // Belarus
            '376' => [6],     // Andorra
            '377' => [8, 9],  // Monaco
            '378' => [10],    // San Marino
            '380' => [9],     // Ukraine
            '381' => [8, 9],  // Serbia
            '382' => [8],     // Montenegro
            '383' => [8],     // Kosovo
            '385' => [8, 9],  // Croatia
            '386' => [8],     // Slovenia
            '387' => [8],     // Bosnia
            '389' => [8],     // North Macedonia
            '420' => [9],     // Czech Republic
            '421' => [9],     // Slovakia
            '423' => [7],     // Liechtenstein
            '500' => [5],     // Falkland Islands
            '501' => [7],     // Belize
            '502' => [8],     // Guatemala
            '503' => [8],     // El Salvador
            '504' => [8],     // Honduras
            '505' => [8],     // Nicaragua
            '506' => [8],     // Costa Rica
            '507' => [8],     // Panama
            '508' => [6],     // Saint Pierre
            '509' => [8],     // Haiti
            '590' => [9],     // Guadeloupe
            '591' => [8],     // Bolivia
            '592' => [7],     // Guyana
            '593' => [9],     // Ecuador
            '594' => [9],     // French Guiana
            '595' => [9],     // Paraguay
            '596' => [9],     // Martinique
            '597' => [7],     // Suriname
            '598' => [8],     // Uruguay
            '599' => [7],     // Curacao
            '670' => [8],     // East Timor
            '672' => [6],     // Antarctica
            '673' => [7],     // Brunei
            '674' => [7],     // Nauru
            '675' => [8],     // Papua New Guinea
            '676' => [5, 7],  // Tonga
            '677' => [5, 7],  // Solomon Islands
            '678' => [5, 7],  // Vanuatu
            '679' => [7],     // Fiji
            '680' => [7],     // Palau
            '681' => [6],     // Wallis and Futuna
            '682' => [5],     // Cook Islands
            '683' => [4],     // Niue
            '685' => [5, 7],  // Samoa
            '686' => [8],     // Kiribati
            '687' => [6],     // New Caledonia
            '688' => [6],     // Tuvalu
            '689' => [8],     // French Polynesia
            '690' => [4],     // Tokelau
            '691' => [7],     // Micronesia
            '692' => [7],     // Marshall Islands
            '850' => [10],    // North Korea
            '852' => [8],     // Hong Kong
            '853' => [8],     // Macau
            '855' => [8, 9],  // Cambodia
            '856' => [8, 10], // Laos
            '880' => [10],    // Bangladesh
            '886' => [9],     // Taiwan
            '960' => [7],     // Maldives
            '961' => [7, 8],  // Lebanon
            '962' => [9],     // Jordan
            '963' => [9],     // Syria
            '964' => [10],    // Iraq
            '965' => [8],     // Kuwait
            '966' => [9],     // Saudi Arabia
            '967' => [9],     // Yemen
            '968' => [8],     // Oman
            '970' => [9],     // Palestine
            '971' => [9],     // UAE
            '972' => [9],     // Israel
            '973' => [8],     // Bahrain
            '974' => [8],     // Qatar
            '975' => [8],     // Bhutan
            '976' => [8],     // Mongolia
            '977' => [10],    // Nepal
            '992' => [9],     // Tajikistan
            '993' => [8],     // Turkmenistan
            '994' => [9],     // Azerbaijan
            '995' => [9],     // Georgia
            '996' => [9],     // Kyrgyzstan
            '998' => [9],     // Uzbekistan
        ];

        // Try to match country codes from longest to shortest
        foreach ([4, 3, 2, 1] as $length) {
            if (strlen($phone) < $length + 7) {
                continue; // Phone too short for this country code length
            }

            $potentialCode = substr($phone, 0, $length);
            $potentialPhone = substr($phone, $length);

            if (isset($countryCodes[$potentialCode])) {
                $expectedLengths = is_array($countryCodes[$potentialCode]) 
                    ? $countryCodes[$potentialCode] 
                    : [$countryCodes[$potentialCode]];

                if (in_array(strlen($potentialPhone), $expectedLengths)) {
                    return [
                        'country_code' => '+' . $potentialCode,
                        'phone_number' => $potentialPhone
                    ];
                }
            }
        }

        // Fallback: assume +1 if no match found
        return [
            'country_code' => '+965',
            'phone_number' => $phone
        ];
    }

    /**
     * Get the sanitized data after validation
     * This should be called AFTER validation is complete
     */
    public function sanitized(): array
    {
        // Data is already sanitized by prepareForValidation
        // Just return the validated data in the format we need
        return [
            'name' => trim($this->input('first_name')) . ' ' . trim($this->input('last_name')),
            'email' => trim(strtolower($this->input('email'))),
            'phone' => $this->input('phone'),
            'country_code' => $this->input('country_code'),
            'country' => ucfirst(trim($this->input('country'))),
            'account_type' => $this->input('account_type', 'Standard'),
            'group_code' => $this->input('group_code'),
        ];
    }

    /**
     * Prepare the data for validation by extracting country code from phone
     */
    protected function prepareForValidation(): void
    {
        $phone = trim($this->input('phone'));
        
        // Remove any spaces, dashes, parentheses, or + from phone
        $phone = preg_replace('/[\s\-\(\)\+]/', '', $phone);

        // Extract country code using known country code database
        $extracted = $this->extractCountryCode($phone);
        
        // Merge the extracted data back into the request
        $this->merge([
            'phone' => $extracted['phone_number'],
            'country_code' => $extracted['country_code'],
        ]);
    }
}
