<?php

namespace App\Http\Controllers;

use Integrations;
use App\Order;
use App\Package_File;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @var Order
     */
    protected $orders;

    /**
     * OrderController constructor.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->orders = $order;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $orders = $this->orders->getByUser($request->user())->get();
        return view('Orders.orderList', compact('orders'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        $order = Order::find($id);
        $user = $request->user();

        if (empty($order->integration)) {
            return view('Orders.orderView', compact('order', 'user'));
        }
        return Integrations::get($order->integration, 'getOrderView', [
            $order,
            $user,
            $request
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return int
     */
    public function processCommand(Request $request, $id)
    {
        $order = $this->orders->findOrFail($id);

        if (!empty($order->integration)) {
            return Integrations::get(
                $order->integration,
                'processCommand',
                [$request->input('command'), $order, $request]
            );
        }

        return 0;
    }

    /**
     * @param null $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downloadPngImage($id = null)
    {
        if ($file = Package_File::find($id)) {
            $url = config('app.CDN') . $file->getUrlDownload();

            $contents = file_get_contents($url);

            header ('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="brand_logo.png"');
            echo $contents;
            die();
        }

        return redirect()->back();
    }
}
