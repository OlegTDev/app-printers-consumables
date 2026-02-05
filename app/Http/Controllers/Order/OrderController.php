<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CommentRequired;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusEnum;
use App\Models\Order\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:' . implode(',', ['admin', Roles::ORDER_APPROVER->value]))
            ->only(['agreed', 'reject', 'ordered', 'received', 'completed']);
        $this->middleware('role:' . implode(',', ['admin', Roles::ORDER_APPROVER->value, Roles::ORDER_EXECUTOR->value]))
            ->only(['ordered', 'received', 'completed']);
    }

    /**
     * @route PUT orders/{order}/agree
     */
    public function agree(Order $order, Request $request)
    {
        $this->validateStatusOrFail($order, OrderStatusEnum::STATUS_PENDING);
        $this->saveOrder($order, OrderStatusEnum::STATUS_AGREED, $request->input('comment'));

        return $this->createRoute($request, 'index')
            ->with('success', 'Заявка успешно согласована!');
    }

    /**
     * @route PUT orders/{order}/reject
     */
    public function reject(Order $order, CommentRequired $request)
    {
        $this->validateStatusOrFail($order, OrderStatusEnum::STATUS_PENDING);
        $this->saveOrder($order, OrderStatusEnum::STATUS_REJECTED, $request->input('comment'));

        return $this->createRoute($request, 'index')
            ->with('success', 'Заявка отказана!');
    }

    /**
     * @route PUT orders/{order}/ordered
     */
    public function ordered(Order $order, Request $request)
    {
        $this->validateStatusOrFail($order, OrderStatusEnum::STATUS_AGREED);
        $this->saveOrder($order, OrderStatusEnum::STATUS_ORDERED, $request->input('comment'));

        return $this->createRoute($request, 'index')
            ->with('success', "Статус заявки изменен на заказан!");
    }

    /**
     * @route PUT orders/{order}/receive
     */
    public function receive(Order $order, Request $request)
    {
        $this->validateStatusOrFail($order, OrderStatusEnum::STATUS_ORDERED);
        $this->saveOrder($order, OrderStatusEnum::STATUS_RECEIVED, $request->input('comment'));

        return $this->createRoute($request, 'index')
            ->with('success', "Статус заявки изменен на получен!");
    }

    /**
     * @route PUT orders/{order}/complete
     */
    public function complete(Order $order, Request $request)
    {
        $this->validateStatusOrFail($order, OrderStatusEnum::STATUS_RECEIVED);
        $this->saveOrder($order, OrderStatusEnum::STATUS_COMPLETED, $request->input('comment'));

        return $this->createRoute($request, 'index')
            ->with('success', 'Исполнение заявки завершено!');
    }

    /**
     * @route PUT orders/{order}/cancel
     */
    public function cancel(Order $order, Request $request)
    {
        if (auth()->user()->isAdmin() || auth()->user()->id == $order->requested_by) {
            $order->setStatus(OrderStatusEnum::STATUS_CANCELLED);
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
        if (!auth()->user()->isAdmin()) {
            if (auth()->user()->id != $order->requested_by) {
                abort(403);
            }
            if ($order->status == OrderStatusEnum::STATUS_COMPLETED) {
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
