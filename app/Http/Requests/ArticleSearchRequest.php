<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleSearchRequest extends FormRequest
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
            'keyword' => 'sometimes|string|max:255',
            'category' => 'sometimes|string',
            'source' => 'sometimes|string',
            'date' => 'sometimes|date_format:Y-m-d',
        ];
    }

    /**
     * Define the body parameters for documentation.
     *
     * @hideFromAPIDocumentation
     * @return array<string, array<string, mixed>>
     */
    public function queryParameters(): array
    {
        return [
            'keyword' => [
                'description' => 'Optional keyword to search for articles.',
                'example' => 'Game',
            ],
            'category' => [
                'description' => 'Optional category to filter articles.',
                'example' => 'technology',
            ],
            'source' => [
                'description' => 'Optional source to filter articles.',
                'example' => 'New York Times',
            ],
            'date' => [
                'description' => 'Optional date to filter articles in YYYY-MM-DD format.',
            ],
        ];
    }
}
