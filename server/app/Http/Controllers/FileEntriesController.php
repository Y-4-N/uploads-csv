<?php


namespace App\Http\Controllers;

use App\Http\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;

class FileEntriesController
{
    public function uploadFile(Request $request)
    {
        $file = Input::file('file');
        $filename = $file->getClientOriginalName();
        $path = hash('sha256', time());

        try {
            if (Storage::disk('uploads')->put($path . '/' . $filename, File::get($file))) {
                $filer = $request->file('file');
                $extension = $filer->getClientOriginalExtension();
                $valid_extension = array("csv");
                if (in_array(strtolower($extension), $valid_extension)) {
                    $filepath = storage_path('/files/uploads/' . $path . '/' . $filename);
                    $filer = fopen($filepath, "r");
                    $importData_arr = array();
                    $i = 0;
                    while (($filedata = fgetcsv($filer, 1000, ";")) !== FALSE) {
                        $num = count($filedata);
                        if ($i == 0) {
                            $i++;
                            continue;
                        }
                        for ($c = 0; $c < $num; $c++) {
                            $importData_arr[$i][] = $filedata [$c];
                        }
                        $i++;
                    }
                    fclose($filer);

                    foreach ($importData_arr as $importData) {

                        $insertData = array(
                            "name" => $importData[1],
                            "cpf" => $importData[2],
                            "birthday" => $importData[3]);
                        Guest::insertData($insertData);
                    }
                    return response()->json(['success' => true], 200, [], JSON_PRETTY_PRINT);
                } else {
                    return response()->json(['success' => false], 400, [], JSON_PRETTY_PRINT);
                }

            }
            return response()->json([
                'success' => true
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}