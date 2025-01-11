<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class CreateCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['required', 'string', 'max:255'],
            'keywords' => ['required', 'string', 'max:255'],
            'og_description' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        if ($errors->has('name')) {
            $response = response()->json([
                'field' => 'name',
                'error' => $errors->first('name'),
            ]);

            throw new ValidationException($validator, $response);
        }

        if ($errors->has('slug')) {
            $response = response()->json([
                'field' => 'slug',
                'error' => $errors->first('slug'),
            ]);

            throw new ValidationException($validator, $response);
        }

        if ($errors->has('description')) {
            $response = response()->json([
                'field' => 'description',
                'error' => $errors->first('description'),
            ]);

            throw new ValidationException($validator, $response);
        }

        if ($errors->has('keywords')) {
            $response = response()->json([
                'field' => 'keywords',
                'error' => $errors->first('keywords'),
            ]);

            throw new ValidationException($validator, $response);
        }

        if ($errors->has('og_description')) {
            $response = response()->json([
                'field' => 'og_description',
                'error' => $errors->first('og_description'),
            ]);

            throw new ValidationException($validator, $response);
        }

        if ($errors->has('parent_id')) {
            $response = response()->json([
                'field' => 'parent_id',
                'error' => $errors->first('parent_id'),
            ]);

            throw new ValidationException($validator, $response);
        }
    }
}
