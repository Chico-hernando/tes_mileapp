<?php

namespace App\Http\Controllers\Api\PackageController;


use App\Http\Controllers\Controller;
use App\Models\Connote;
use App\Models\DestinationData;
use App\Models\HistoryTransport;
use App\Models\KoliData;
use App\Models\OriginData;
use App\Models\Transaction;
use App\Models\TransportApproval;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PackageController extends Controller {

    public function createPackage(Request $request) {   

        $validate = Validator::make($request->all(), [
            'customer_name' => 'required',
            'customer_code' => 'required',
            'transaction_amount' => 'required', 
            'transaction_discount' => 'required', 
            'transaction_payment_type' => 'required', 
            'transaction_state' => 'required', 
            'location_id' => 'required', 
            'organization_id' => 'required|integer', 
            'transaction_payment_type_name' => 'required', 
            'transaction_cash_amount' => 'required', 
            'transaction_cash_change' => 'required', 
            'customer_attribute' => 'required',
            'customer_attribute.Nama_Sales' => 'required',
            'customer_attribute.TOP' => 'required',
            'customer_attribute.Jenis_Pelanggan' => 'required', 
            'connote.connote_service' => 'required', 
            'connote.connote_service_price' => 'required|integer', 
            'connote.connote_amount' => 'required|integer', 
            'connote.connote_order' => 'required|integer', 
            'connote.connote_state_id' => 'required|integer', 
            'connote.actual_weight' => 'required|integer', 
            'connote.volume_weight' => 'required|integer',  
            'connote.connote_sla_day' => 'required', 
            'connote.source_tariff_db' => 'required', 
            'connote.id_source_tariff' => 'required', 
            'origin_data.customer_name' => 'required', 
            'origin_data.customer_address' => 'required', 
            'origin_data.customer_phone' => 'required', 
            'origin_data.customer_zip_code' => 'required', 
            'origin_data.zone_code' => 'required', 
            'destination_data.customer_name' => 'required', 
            'destination_data.customer_address' => 'required', 
            'destination_data.customer_phone' => 'required', 
            'destination_data.customer_zip_code' => 'required', 
            'destination_data.zone_code' => 'required', 
            'koli_data.koli_length' => 'sometimes|required|integer',  
            'koli_data.koli_chargeable_weight' => 'sometimes|required|integer', 
            'koli_data.koli_width' => 'sometimes|required|integer', 
            'koli_data.koli_height' => 'sometimes|required|integer', 
            'koli_data.koli_description' => 'sometimes|required', 
            'koli_data.koli_volume' => 'sometimes|required|integer', 
            'koli_data.koli_weight' => 'sometimes|required|integer', 
            'currentLocation.name' => 'required', 
            'currentLocation.code' => 'required', 
            'currentLocation.type' => 'required' 
        ]);


        if ($validate->fails()) {
            return $this->responseError("Data not valid", $validate->errors()->first());
        }

        $req = $request->all();

        $transaction = $connote = $origin_data = $destination_data = $koli_data = array();
        
        $transaction["transaction_id"] = Str::uuid()->toString();
        $transaction["connote_id"] = Str::uuid()->toString();
        $transaction["created_at"] = $this->datetimeNow();
        $transaction["updated_at"] = $this->datetimeNow();
        $transaction["transaction_order"] = Transaction::count();


        $weight = $volume = 0;
        foreach ($req as $key => $values) {
            
            switch ($key) {
                case "connote":
                    $connote["connote_id"] = $transaction["connote_id"];
                    $connote["connote_order"] = Connote::count();
                    $connote["connote_code"] = $this->generateAWB();
                    $connote["transaction_id"] = $transaction["transaction_id"];
                    $connote["created_at"] = $this->datetimeNow();
                    $connote["updated_at"] = $this->datetimeNow();
                    foreach ($values as $keyConnote => $valueConnote) {
                        $connote[$keyConnote] = $valueConnote;
                    }
                    break;
                case "origin_data":
                    foreach ($values as $keyOriginData => $valueOriginData) {
                        $origin_data[$keyOriginData] = $valueOriginData;

                        if ($keyOriginData == "zone_code") {
                            $connote["zone_code_from"] = $valueOriginData;

                            $transaction["transaction_code"] = $valueOriginData.$this->dateTransaction().$transaction["transaction_order"];
                        }
                    }
                    $transaction[$key] = $origin_data;
                    break;
                case "destination_data":
                    foreach ($values as $keyDestinationData => $valueDestinationData) {
                        $destination_data[$keyDestinationData] = $valueDestinationData;

                        if ($keyDestinationData == "zone_code") {
                            $connote["zone_code_to"] = $valueDestinationData;
                        }
                    }
                    $transaction[$key] = $destination_data;
                    break;
                case "koli_data":
                    $row = array();
                    foreach ($values as $i => $valueKoliData) {
                        $row2 = array();
                        $row2["connote_id"] = $transaction["connote_id"];
                        $row2["created_at"] = $this->datetimeNow();
                        $row2["updated_at"] = $this->datetimeNow();
                        $row2["koli_id"] = Str::uuid()->toString();
                        $row2["koli_code"] = $connote["connote_code"].".".$i+1;
                        $row2["awb_url"] = "https:\/\/tracking.mile.app\/label\/".$row2["koli_code"];
                        foreach ($valueKoliData as $keyKoliData2 => $valueKoliData2) {
                            $row2[$keyKoliData2] = $valueKoliData2;

                            if ($keyKoliData2 == "koli_weight") {
                                $weight += $valueKoliData2;
                            }

                            if ($keyKoliData2 == "koli_volume") {
                                $volume += $valueKoliData2;
                            }
                        }
                        $row[] = $row2;
                    }
                    $koli_data = $row;
                    $connote["total_package"] = count($row);
                    $connote["actual_weight"] = $weight;
                    $connote["volume_weight"] = $volume;
                    $connote["actual_weight"] = $weight;
                    break;
                default:
                $transaction[$key] = $values;
            }

        }

        $connote["location_name"] = $transaction["currentLocation"]["name"];
        $connote["location_type"] = $this->generateLocationType($connote["location_name"]);
        
        try {

        Transaction::create($transaction);
        Connote::create($connote);

        foreach ($koli_data as $value) {
            KoliData::create($value);

        }

        $result = $transaction;
        $result["connote"] = $connote;
        $result["koli_data"] = $koli_data;
        return $this->responseSuccess("Success create transaction",$result);
    } catch (Exception $e) {
        return $this->responseError("Failed create transport",$e->getMessage());

    }

    }

    public function getPackage() {
        try {
        $transactions = Transaction::all(); 

        foreach ($transactions as $key => $transaction) {
            $transactions[$key]["connote"] = Connote::where("connote_id", $transaction["connote_id"])->first();
            $transactions[$key]["koli_data"] = KoliData::where("connote_id", $transaction["connote_id"])->get();
        }

        return $this->responseSuccess("Succesfully get data", $transactions);
    } catch (Exception $e) {
        return $this->responseError("Failed get data", $e->getMessage());

    }
    }

    public function getPackageById($id) {
        try {
        $transactions = Transaction::where("transaction_id", $id)->first(); 

        if (empty($transactions)) {
            return $transactions;
        }

        $transactions["connote"] = Connote::where("connote_id", $transactions["connote_id"])->first();
        $transactions["koli_data"] = KoliData::where("connote_id", $transactions["connote_id"])->get();

        return $this->responseSuccess("Succesfully get data", $transactions);
    } catch (Exception $e) {
        return $this->responseError("Failed get data", $e->getMessage());

    }
    }

    public function updateSinglePackage(Request $request, $id) {
        try {

        $transactions = Transaction::where("transaction_id", $id)->first(); 

        if (empty($transactions)) {
            return $transactions;
        }

        $req = $request->all();

        $transactions->update($req);

        return $this->responseSuccess("Success update transaction", $transactions);
    } catch (Exception $e) {
        return $this->responseError("Failed update transaction", $e->getMessage());

    }

    }

    public function updatePackage(Request $request, $id) { 
        $validate = Validator::make($request->all(), [
            'customer_name' => 'required',
            'customer_code' => 'required',
            'transaction_amount' => 'required', 
            'transaction_discount' => 'required', 
            'transaction_payment_type' => 'required', 
            'transaction_state' => 'required', 
            'location_id' => 'required', 
            'organization_id' => 'required|integer', 
            'transaction_payment_type_name' => 'required', 
            'transaction_cash_amount' => 'required', 
            'transaction_cash_change' => 'required', 
            'customer_attribute' => 'required',
            'customer_attribute.Nama_Sales' => 'required',
            'customer_attribute.TOP' => 'required',
            'customer_attribute.Jenis_Pelanggan' => 'required', 
            'connote.connote_service' => 'required', 
            'connote.connote_service_price' => 'required|integer', 
            'connote.connote_amount' => 'required|integer', 
            'connote.connote_order' => 'required|integer', 
            'connote.connote_state_id' => 'required|integer', 
            'connote.actual_weight' => 'required|integer', 
            'connote.volume_weight' => 'required|integer',  
            'connote.connote_sla_day' => 'required', 
            'connote.source_tariff_db' => 'required', 
            'connote.id_source_tariff' => 'required', 
            'origin_data.customer_name' => 'required', 
            'origin_data.customer_address' => 'required', 
            'origin_data.customer_phone' => 'required', 
            'origin_data.customer_zip_code' => 'required', 
            'origin_data.zone_code' => 'required', 
            'destination_data.customer_name' => 'required', 
            'destination_data.customer_address' => 'required', 
            'destination_data.customer_phone' => 'required', 
            'destination_data.customer_zip_code' => 'required', 
            'destination_data.zone_code' => 'required', 
            'koli_data.koli_length' => 'sometimes|required|integer',  
            'koli_data.koli_chargeable_weight' => 'sometimes|required|integer', 
            'koli_data.koli_width' => 'sometimes|required|integer', 
            'koli_data.koli_height' => 'sometimes|required|integer', 
            'koli_data.koli_description' => 'sometimes|required', 
            'koli_data.koli_volume' => 'sometimes|required|integer', 
            'koli_data.koli_weight' => 'sometimes|required|integer', 
            'currentLocation.name' => 'required', 
            'currentLocation.code' => 'required', 
            'currentLocation.type' => 'required' 
        ]);

        if ($validate->fails()) {
            return $this->responseError("Data not valid", $validate->errors()->first());
        }

        $req = $request->all();


        $transactions = Transaction::where("transaction_id", $id)->first(); 

        if (empty($transactions)) {
            return $transactions;
        }

        $req = $request->all();

        try {
        $transactions->update($req);

        // return $transactions;
        return $this->responseSuccess("Success update transaction", $transactions);
    } catch (Exception $e) {
        return $this->responseError("Failed update transaction", $e->getMessage());

    }
    }

    public function deletePackage($id) {

        $transactions = Transaction::find($id);

        if (empty($transactions)) {
            return $transactions;
        }

        try {
        $transactions->delete();

        return $this->responseSuccess("Success delete transaction", "");
    } catch (Exception $e) {
        return $this->responseError("Failed delete transaction", $e->getMessage());

    }

        // return "data berhasil di delete";
    }

    public function generateAWB() {
        $result = "AWB001002";

        $result = $result.$this->datetimeAWB();

        return $result;
    }

    public function generateLocationType($location) {

        $result = "";

    switch (true) {
        case stripos($location, "hub") !== false:
            $result = "HUB";
            break;
        case stripos($location, "base") !== false:
            $result = "BASE";
            break;
        default:
            $result = "Tidak ada kata yang cocok.";
            break;
    }

    return $result;

    }
}