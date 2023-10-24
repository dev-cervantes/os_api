<?php

namespace App\Http\Requests\Os;

use Illuminate\Foundation\Http\FormRequest;

class OsUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return (new OsStoreRequest)->rules();
    }

    public function messages(): array
    {
        return (new OsStoreRequest)->messages();
    }
}
