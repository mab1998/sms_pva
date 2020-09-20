<?php

namespace App\Http\Controllers;

use App\SMSHistory;
use App\SMSInbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;
use Maatwebsite\Excel\Facades\Excel;

class CommonDataController extends Controller
{

    //======================================================================
    // getCsvFileInfo Function Start Here
    //======================================================================
    public function getCsvFileInfo(Request $request)
    {

        try {

            $file_extension = Input::file('import_numbers')->getClientOriginalExtension();
            $supportedExt   = array('csv', 'xls', 'xlsx');

            if (isset($supportedExt) && is_array($supportedExt) && !in_array_r(strtolower($file_extension), $supportedExt)) {
                return response()->json(['status' => 'error', 'message' => language_data('Insert Valid Excel or CSV file')]);
            }

            $all_data = Excel::load($request->import_numbers)->noHeading()->all()->toArray();

            if (isset($all_data) && is_array($all_data) && array_empty($all_data)) {
                return response()->json(['status' => 'error', 'message' => 'Empty Field']);
            }

            $counter = "A";

            if ($request->header_exist == 'true') {

                $header = array_shift($all_data);

                foreach ($header as $key => $value) {
                    if (!$value) {
                        $header[$key] = "Column " . $counter;
                    }

                    $counter++;
                }

            } else {

                $header_like = $all_data[0];

                $header = array();

                foreach ($header_like as $h) {
                    array_push($header, "Column " . $counter);
                    $counter++;
                }

            }

            $all_data = array_map(function ($row) use ($header) {

                return array_combine($header, $row);

            }, $all_data);


            return response()->json(["status" => "success", "data" => $all_data]);

        } catch (LaravelExcelException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

    }

    //======================================================================
    // getClientCsvFileInfo Function Start Here
    //======================================================================
    public function getClientCsvFileInfo(Request $request)
    {

        try {

            $file_extension = Input::file('import_client')->getClientOriginalExtension();
            $supportedExt   = array('csv', 'xls', 'xlsx');

            if (isset($supportedExt) && is_array($supportedExt) && !in_array_r(strtolower($file_extension), $supportedExt)) {
                return response()->json(['status' => 'error', 'message' => language_data('Insert Valid Excel or CSV file')]);
            }

            $all_data = Excel::load($request->import_client)->noHeading()->all()->toArray();

            if ($all_data && is_array($all_data) && array_empty($all_data)) {
                return response()->json(['status' => 'error', 'message' => 'Empty Field']);
            }

            $counter = "A";

            if ($request->header_exist == 'true') {

                $header = array_shift($all_data);

                foreach ($header as $key => $value) {
                    if (!$value) {
                        $header[$key] = "Column " . $counter;
                    }

                    $counter++;
                }

            } else {

                $header_like = $all_data[0];

                $header = array();

                foreach ($header_like as $h) {
                    array_push($header, "Column " . $counter);
                    $counter++;
                }

            }

            $all_data = array_map(function ($row) use ($header) {

                return array_combine($header, $row);

            }, $all_data);


            return response()->json(["status" => "success", "data" => $all_data]);

        } catch (LaravelExcelException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

    }



}
