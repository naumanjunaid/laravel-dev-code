<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslationRequest extends FormRequest
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
        $translationId = $this->route('translation');
        $isUpdate = $translationId !== null;

        return [
            'key' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('translations', 'key')
                    ->where('locale_id', $this->input('locale_id'))
                    ->ignore($translationId),
            ],
            'content' => [$isUpdate ? 'sometimes' : 'required', 'string'],
            'locale_id' => [$isUpdate ? 'sometimes' : 'required', 'exists:locales,id'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['exists:tags,id'],
        ];
    }
}
