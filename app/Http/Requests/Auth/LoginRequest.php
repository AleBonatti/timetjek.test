<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'personnummer' => ['required', 'string', 'regex:/^\d{6,8}-?\d{4}$/', $this->luhnRule()],
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'personnummer.regex' => 'The personnummer format is invalid.',
        ];
    }

    /**
     * Returns a closure rule that validates the Luhn checksum of a personnummer.
     * Accepts both 10-digit (YYMMDD-NNNN) and 12-digit (YYYYMMDD-NNNN) formats.
     */
    private function luhnRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            // Normalise: strip dash, then strip leading century (YY) if 12-digit
            $digits = str_replace('-', '', $value);
            if (strlen($digits) === 12) {
                $digits = substr($digits, 2); // keep only YYMMDDNNNN
            }

            if (strlen($digits) !== 10 || !ctype_digit($digits)) {
                $fail('The personnummer format is invalid.');
                return;
            }

            // Luhn algorithm over the first 9 digits; 10th digit is the check digit
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                $n = (int) $digits[$i] * (($i % 2 === 0) ? 2 : 1);
                $sum += ($n > 9) ? $n - 9 : $n;
            }

            $checkDigit = (10 - ($sum % 10)) % 10;

            if ($checkDigit !== (int) $digits[9]) {
                $fail('The personnummer checksum is invalid.');
            }
        };
    }
}
