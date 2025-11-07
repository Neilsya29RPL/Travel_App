<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\BookingService;
use App\Services\BudgetService;

class BookingController extends Controller
{
    private $bookingService;
    private $budgetService;

    public function __construct(BookingService $bookingService, BudgetService $budgetService)
    {
        $this->bookingService = $bookingService;
        $this->budgetService = $budgetService;
    }

    public function index()
    {
        $userId = Auth::id();
        $data = $this->bookingService->getBookingData($userId);

        return view('booking.index', $data);
    }

    public function create(Request $request)
    {
        $userId = Auth::id();
        $data = $request->all();

        $result = $this->bookingService->createBooking($userId, $data);

        if ($result['error']) {
            return redirect()->route($result['redirect'])->with('error', $result['message']);
        }

        return redirect()->route('bookings.index')->with('status', $result['message']);
    }

    public function pay(Request $request)
    {
        $userId = Auth::id();
        $data = $request->all();

        $result = $this->bookingService->processPayment($userId, $data);

        if ($result['error']) {
            return redirect()->route($result['redirect'])->with('error', $result['message']);
        }

        return redirect()->route('bookings.show', ['booking' => $result['booking_id']])
            ->with('status', $result['message']);
    }

    public function list()
    {
        $userId = Auth::id();
        $bookings = $this->bookingService->listBookings($userId);

        return view('booking.list', compact('bookings'));
    }

    public function show($bookingId)
    {
        $userId = Auth::id();
        $data = $this->bookingService->getBookingDetails($userId, $bookingId);

        if (!$data['booking']) {
            return redirect()->route('bookings.index')->with('error', 'Booking tidak ditemukan.');
        }

        return view('booking.detail', $data);
    }

    public function checkout(Request $request)
    {
        $userId = Auth::id();
        $bookingId = $request->query('booking');

        $data = $this->bookingService->getCheckoutData($userId, $bookingId);

        if (!$data['booking']) {
            return redirect()->route('bookings.index')->with('error', 'Booking tidak ditemukan.');
        }

        return view('payment.checkout', $data);
    }
}
