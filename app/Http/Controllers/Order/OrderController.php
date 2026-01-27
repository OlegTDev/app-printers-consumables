<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CommentRequired;
use App\Models\Order\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{


    public function __construct()
    {
        $this->middleware('role:admin,order-approver')
            ->only(['approve', 'reject', 'completed']);
    }

    /**
     * @route PUT orders/{order}/approve
     */
    public function approve(Order $order, Request $request)
    {
        $this->validateStatusOrFail($order, Order::STATUS_PENDING);
        $this->saveOrder($order, Order::STATUS_IN_PROGRESS, $request->input('comment'));

        return $this->createRoute($request, 'index')
            ->with('success', 'Заявка успешно согласована!');
    }

    /**
     * @route PUT orders/{order}/reject
     */
    public function reject(Order $order, CommentRequired $request)
    {
        $this->validateStatusOrFail($order, Order::STATUS_PENDING);
        $this->saveOrder($order, Order::STATUS_REJECTED, $request->input('comment'));

        return $this->createRoute($request, 'index')
            ->with('success', 'Заявка отказана!');
    }

    /**
     * @route PUT orders/{order}/completed
     */
    public function completed(Order $order, Request $request)
    {
        $this->validateStatusOrFail($order, Order::STATUS_IN_PROGRESS);
        $this->saveOrder($order, Order::STATUS_COMPLETED, $request->input('comment'));
        
        return $this->createRoute($request, 'index')
            ->with('success', 'Исполнение заявки завершено!');
    }

    /**
     * @route PUT orders/{order}/cancel
     */
    public function cancel(Order $order, Request $request)
    {        
        if (auth()->user()->hasRole('admin') || auth()->user()->id == $order->requested_by) {
            $order->setStatus(Order::STATUS_CANCELLED);
            return $this->createRoute($request, 'index')
                ->with('success', 'Заявка отменена!');
        }
        abort(403);
    }

    /**
     * @route DELETE orders/{order}
     */
    public function destroy(Order $order, Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            if (auth()->user()->id != $order->requested_by) {
                abort(403);
            }
            if ($order->status == Order::STATUS_COMPLETED) {
                abort(500, "Невозможно удалить со статусом {$order->status}.");
            }
        }
        $order->delete();
        return $this->createRoute($request, 'index')
            ->with('success', value: 'Заявка была удалена!');
    }

    private function createRoute(Request $request, string $action): RedirectResponse
    {
        $context = $request->get('context');
        $route = $context ? "$context.$action" : $action;
        return redirect()->route($route);
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
