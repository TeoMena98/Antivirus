<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Appointment;
use App\Time;
use App\User;
use App\Booking;
use App\Prescription;
use App\Mail\AppointmentMail;
use Carbon\Carbon;
use Spatie\GoogleCalendar\Event;

use Illuminate\Support\Str;
class FrontEndController extends Controller
{
    public function index(Request $request)
    {
        // Set timezone
        date_default_timezone_set('America/New_York');
        // If there is set date, find the doctors
        if (request('date')) {
            $formatDate = date('m-d-Y', strtotime(request('date')));
            $doctors = Appointment::where('date', $formatDate)->get();
            return view('welcome', compact('doctors', 'formatDate'));
        };
        // Return all doctors avalable for today to the welcome page
        $doctors = Appointment::where('date', date('m-d-Y'))->get();
        return view('welcome', compact('doctors'));
    }

    public function show($doctorId, $date)
    {
        $appointment = Appointment::where('user_id', $doctorId)->where('date', $date)->first();
        $times = Time::where('appointment_id', $appointment->id)->where('status', 0)->get();
        $user = User::where('id', $doctorId)->first();
        $doctor_id = $doctorId;
        return view('appointment', compact('times', 'date', 'user', 'doctor_id'));
    }

    public function store(Request $request)
    {
        // Set timezone
        date_default_timezone_set('America/New_York');

        $request->validate(['time' => 'required']);
        $check = $this->checkBookingTimeInterval();
        foreach ($check as $key => $checkos) {
           
        
      
        if ($checkos->Conteo == 3) {
            return redirect()->back()->with('errMessage', 'Ya superaste el limite de tutorias. Por favor verifica tu correo!');
        }
    };
        $doctorId = $request->doctorId;
    
        $time = Carbon::createFromFormat('h A',$request->time);
      
        $appointmentId = $request->appointmentId;
        $date = Carbon::createFromFormat('m-d-Y', $request->date);


// dd();
        $doctor = User::where('id', $doctorId)->first();
        Time::where('appointment_id', $appointmentId)->where('time', $time)->update(['status' => 1]);

$startTime = Carbon::parse(date('d-M-Y',strtotime($date)).' '.date('g:i A',strtotime($time)));
$endTime = (clone $startTime)->addMinutes(30);
$tutor = "Tutoria con".$doctor->name;
dd($startTime);
$e = new Event;
dd(auth()->user()->email);
dd($e->conferenceData =['createRequest' =>[
    'requestId'=>Str::random(30).time(),
]]);




dd($e);


Booking::create([
    'user_id' => auth()->user()->id,
    'doctor_id' => $doctorId,
    'time' => $time,
    'date' => $date,
    'status' => 0
]);
        // Event::create([
        //     'name'=> $tutor,
        //     'startDateTime' => $startTime,
        //     'endDateTime' => $endTime
        // ]);

       
     



        // Send email notification
        $mailData = [
            'name' => auth()->user()->name,
            'time' => $time,
            'date' => $date,
            'doctorName' => $doctor->name
        ];
        try {
            \Mail::to(auth()->user()->email)->send(new AppointmentMail($mailData));
        } catch (\Exception $e) {
        }

        return redirect()->back()->with('message', 'Tu tutoria ha sido agendada para el dia ' . $date . ' a las ' . $time . ' con ' . $doctor->name . '.');
    }


    

    // check if user already make a booking.
    public function checkBookingTimeInterval()
    {
         $checkeo = Booking::selectRaw('Count(id) As Conteo')
            ->where('user_id', auth()->user()->id)
            ->whereDate('created_at', date('y-m-d'))
            ->get();

            return $checkeo;
    }

    public function myBookings()
    {
        $appointments = Booking::latest()->where('user_id', auth()->user()->id)->get();
        return view('booking.index', compact('appointments'));
    }

    public function myPrescription()
    {
        $prescriptions = Prescription::where('user_id', auth()->user()->id)->get();
        return view('my-prescription', compact('prescriptions'));
    }
}
