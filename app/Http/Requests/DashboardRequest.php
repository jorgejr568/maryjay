<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name" => "required|min:3",
            "period_from" => "nullable|date_format:Y-m-d",
            "period_to" => "nullable|date_format:Y-m-d",
            "queries" => "required|array|min:1",
            "metadata_rule_metadata" => "nullable|array",
            "metadata_rule_query" => "nullable|array"
        ];
    }
}
