<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CommentRequired;
use App\Models\Order\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    /**
     * @route PUT orders/{order}/approve
     */
    public function approve(Order $order, Request $request)
    {
        $this->validateStatusOrFail($order, Order::STATUS_PENDING);
        $this->saveOrder($order, Order::STATUS_IN_PROGRESS, $request->input('comment'));

        return redirect()->route('spare-parts.index')
            ->with('success', 'Заявка успешно согласована!');
    }

    /**
     * @route PUT orders/{order}/reject
     */
    public function reject(Order $order, CommentRequired $request)
    {
        $this->validateStatusOrFail($order, Order::STATUS_PENDING);
        $this->saveOrder($order, Order::STATUS_REJECTED, $request->input('comment'));

        return redirect()->route('spare-parts.index')
            ->with('success', 'Заявка отказана!');
    }

    /**
     * @route PUT orders/{order}/completed
     */
    public function completed(Order $order, Request $request)
    {
        $this->validateStatusOrFail($order, Order::STATUS_IN_PROGRESS);
        $this->saveOrder($order, Order::STATUS_COMPLETED, $request->input('comment'));
        
        return redirect()->route('spare-parts.index')
            ->with('success', 'Исполнение заявки завершено!');
    }


    private function validateStatusOrFail(Order $order, string $correctStatus)
    {
        if ($order->status != $correctStatus) {
            abort(500, "Согласование возможно только со статусом $correctStatus, текущий статус $order->status!");
        }
    }

    private function saveOrder(Order $order, string $status, ?string $comment)
    {
        $order->status = $status;
        $order->comment = $comment;
        $order->save();
    }

}
