<?php

namespace App\Http\Controllers;

use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
	public function getFrontendHome()
	{
		return view('Home.frontendHome', $this->setCurrentData());
	}

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
	public function ticketForm()
    {
        return view('Support.supportTicketPublicCreate', $this->setCurrentData());
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function ticketFormPost(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required'
        ]);

        return back()->with('status', 'Your message has been send, we will reply your message!');
    }

    /**
     * @return array
     */
    protected function setCurrentData()
    {
        $data = [
            'currency' => Controller::setCurrency(),
            'default_currency' => Controller::setDefaultCurrency(),
            'cart' => Controller::formatCartData()
        ];

        $bascket = CommonController::factory()->registerBasketInfo();
        $data['basketGrendTotal'] = 0;

        if ($bascket) {
            $data['basketGrendTotal'] = $bascket['basketGrendTotal'];
        }

        return $data;
    }
}
