<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Rules\Cyrillic;
use Illuminate\Support\Facades\DB;

class FIOController extends Controller
{
	public function checkFIO(Request $request)
	{
        $messages = [
            'last_name.required' => 'Не заполнено обязательное поле last_name',
            'first_name.required' => 'Не заполнено обязательное поле first_name'
        ];

        $validator = Validator::make($request->all(), [
            'last_name' => ['required', new Cyrillic],
            'first_name' => ['required', new Cyrillic],
            'surname' => ['nullable', new Cyrillic]
        ], $messages);

        if ($validator->fails()) {
            return json_encode(["success" => false, "message" => $validator->errors() ]);
        }

        return json_encode(["success" => true]);
    }

    public function saveFIO(Request $request)
    {
        $checkFIO = $this->checkFIO($request);

        if(json_decode($checkFIO)->success === false) {
            return $checkFIO;
        }

        $fio = $request->all();

        $existing_fio = DB::table('fio')
                            ->where('last_name', $fio['last_name'])
                            ->where('first_name', $fio['first_name']);

        if(isset($fio['surname'])) {
            $existing_fio = $existing_fio->where('surname', $fio['surname']);
        } else {
            $existing_fio = $existing_fio->where('surname', null);
        }

        if($existing_fio->first() != null) {
            return json_encode(["success" => false, "message" => ["unique_check" => "Такие ФИО уже существуют."]]);
        }

        DB::table('fio')->insert($fio);

        return json_encode(["success" => true]);
    }
}
