<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePairwiseComparisonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['super_admin', 'admin_univ']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'raw_values' => ['required', 'array'],
            'raw_values.*' => [
                'required',
                'numeric',
                'min:0.001',
                'max:9999',
                function ($attribute, $value, $fail) {
                    if ($value <= 0 || $value == null) {
                        $fail('Setiap bobot harus positif (> 0)');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validators.
     */
    public function messages(): array
    {
        return [
            'raw_values.required' => 'Data bobot tidak lengkap.',
            'raw_values.*.required' => 'Setiap bobot kriteria harus diisi.',
            'raw_values.*.numeric' => 'Bobot harus berupa angka.',
            'raw_values.*.min' => 'Setiap bobot harus minimal 0.001.',
            'raw_values.*.max' => 'Setiap bobot tidak boleh lebih dari 9999.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $rawValues = $this->input('raw_values', []);

        // Sanitize each value
        foreach ($rawValues as $key => $value) {
            if ($value !== null) {
                $rawValues[$key] = (float) $value;
            }
        }

        $this->merge(['raw_values' => $rawValues]);
    }
}
