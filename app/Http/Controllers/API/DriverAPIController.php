<?php

namespace App\Http\Controllers\API;


use App\Models\Driver;
use App\Repositories\DriverRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use Flash;
/*NEW-CODE-START-88*/
use DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
/*NEW-CODE-END-88*/

/**
 * Class DriverController
 * @package App\Http\Controllers\API
 */

class DriverAPIController extends Controller
{
    /** @var  DriverRepository */
    private $driverRepository;

    public function __construct(DriverRepository $driverRepo)
    {
        $this->driverRepository = $driverRepo;
    }

    /**
     * Display a listing of the Driver.
     * GET|HEAD /drivers
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->driverRepository->pushCriteria(new RequestCriteria($request));
            $this->driverRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $drivers = $this->driverRepository->all();

        return $this->sendResponse($drivers->toArray(), 'Drivers retrieved successfully');
    }

    /**
     * Display the specified Driver.
     * GET|HEAD /drivers/{id}
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var Driver $driver */
        if (!empty($this->driverRepository)) {
            $driver = $this->driverRepository->findWithoutFail($id);
        }

        if (empty($driver)) {
            return $this->sendError('Driver not found');
        }

        return $this->sendResponse($driver->toArray(), 'Driver retrieved successfully');
    }

    /*NEW-CODE-START-88*/
    //Update driver location
    public function updateDriverLocation(Request $request)
    {
        $driver_id = $request->input('driver_id');
        $driver_lat = $request->input('driver_lat');
        $driver_long = $request->input('driver_long');

        $validator = Validator::make($request->all(), [
            'driver_id' => 'required',
            'driver_lat' => 'required',
            'driver_long' => 'required'
        ]);

        if ($validator->fails()) {   

            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;

        } else {

            $affectedRow = DB::table('drivers')
            ->where('user_id', $driver_id)
            ->update(['driver_lat' => $driver_lat,'driver_long' => $driver_long,'updated_at'=>Carbon::now()->toDateTimeString()]);

            if($affectedRow == true){
                $response_array = array(
                    'success' => true, 
                    'message' => 'location updated successfully'
                );

                $response_code = 200;
            }else{
                $response_array = array(
                    'success' => false, 
                    'message' => 'Unable to update location'
                );

                $response_code = 200;
            }
        }
        $response = response()->json($response_array, $response_code);
        return $response;
    }
    /*NEW-CODE-END-88*/    
}

