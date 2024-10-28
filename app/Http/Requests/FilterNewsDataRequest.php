<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterNewsDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'coins' => 'required|array',
            'coins.*' => 'required|integer|exists:coins,id',
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
        ];
    }

    public function messages()
    {
        return [
            'coins.required' => 'En az bir coin seçmelisiniz.',
            'coins.array' => 'Coins alanı bir dizi olmalıdır.',
            'coins.*.required' => 'Her coin seçeneği gereklidir.',
            'coins.*.integer' => 'Coin ID\'leri geçerli bir sayı olmalıdır.',
            'coins.*.exists' => 'Seçtiğiniz coin bulunamadı.',
            'start_date.required' => 'Başlangıç tarihi gereklidir.',
            'start_date.date' => 'Başlangıç tarihi geçerli bir tarih olmalıdır.',
            'end_date.required' => 'Bitiş tarihi gereklidir.',
            'end_date.date' => 'Bitiş tarihi geçerli bir tarih olmalıdır.',
            'end_date.after_or_equal' => 'Bitiş tarihi, başlangıç tarihinden sonra veya ona eşit olmalıdır.',
        ];
    }
}
