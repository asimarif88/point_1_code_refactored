<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;
use Illuminate\Support\Facades\Validator;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if($user_id = $request->get('user_id')) {

            $response = $this->repository->getUsersJobs($user_id);

        }
        elseif($request->__authenticatedUser->user_type == env('ADMIN_ROLE_ID') || $request->__authenticatedUser->user_type == env('SUPERADMIN_ROLE_ID'))
        {
            $response = $this->repository->getAll($request);
        }
        return response($response, 200)->header('Content-Type', 'text/html');
        // Asim Comment ( I have added the status code and the header so it will help to read the response)
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return response($job, 200)->header('Content-Type', 'text/html');
        // Asim Comment ( I have added the status code and the header so it will help to read the response)
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->store($request->__authenticatedUser, $data);

        return response()->json($response);
         // Asim Comment ( I have added json function to read the response)

    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request,$id) // Asim Comment (First parameter must be request according to the Laravel practice)
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        return response()->json($response);
        // Asim Comment ( I have added json function to read the response)
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();

        $response = $this->repository->storeJobEmail($data);

        return response()->json($response);
        // Asim Comment ( I have added json function to read the response)
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        $user_id = $request->get('user_id');
        // Asim comment (checking user id have set) 
        if($user_id) {
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return response()->json($response);
            // Asim Comment ( I have added json function to read the response)
        }
        // Asim comment ( replace null with proper response)
        return response()->json(['status'=>'fail','message'=>'Unable to fetch the history']);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJob($data, $user);

        return response()->json($response);
        // Asim Comment ( I have added json function to read the response)
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJobWithId($data, $user);

        return response()->json($response);
        // Asim Comment ( I have added json function to read the response)
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->cancelJobAjax($data, $user);

        return response()->json($response);
        // Asim Comment ( I have added json function to read the response)
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->endJob($data);

        return response()->json($response);
        // Asim Comment ( I have added json function to read the response)

    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);

        return response()->json($response);
        // Asim Comment ( I have added json function to read the response)

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->getPotentialJobs($user);

        return response()->json($response);
        // Asim Comment ( I have added json function to read the response)
    }

    public function distanceFeed(Request $request)
    {
        // Asim comment (adding validate library)
        $validator = Validator::make($request,[
            'distance'=>'required',
            'time'=>'required',
            'jobid'=>'required|int',
            'session_time'=>'required',
            'flagged'=>'required',
            'manually_handled'=>'required',
            'by_admin'=>'required',
            'admincomment'=>'required'            
         ]);

         

         if ($validator->fails()) {
            Session::flash('error', $validator->messages()->first());
            return redirect()->back()->withInput();
         }
         $jobid     = $request->get('jobid');
         $time      = $request->get('time');
         $distance  = $request->get('distance');
         $admincomment  = $request->get('admincomment');
         $flagged  = $request->get('flagged') ? 'yes' : 'no';
         $manually_handled  = $request->get('manually_handled') ? 'yes' : 'no';
         $by_admin  = $request->get('by_admin') ? 'yes' : 'no';
         $session  = $request->get('session_time');
         // Asim Comment (After getting value update the value into the database)
         
         $affectedRows = Distance::where('job_id', '=', $jobid)->update(array('distance' => $distance, 'time' => $time));
         $affectedRows1 = Job::where('id', '=', $jobid)->update(array('admin_comments' => $admincomment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin));
        return response()->json(['status'=>'success','message'=>'Record updated!']);
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response()->json($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response()->json(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response()->json(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response()->json(['success' => $e->getMessage()]);
        }
    }

}
