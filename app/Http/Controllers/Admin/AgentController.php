<?php

namespace App\Http\Controllers\Admin;

use App\Agents\PalazAdminAgent;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

final class AgentController extends Controller
{
    public function chat(Request $request, PalazAdminAgent $agent)
    {
        $data=$request->validate(['message'=>['required','string','max:1200']]);
        $result=$agent->interpret($data['message']);
        $action=$result['action'] ?? null;

        if (is_array($action) && ($action['confirm_required'] ?? false)) {
            session(['admin_agent_pending'=>$action]);
        } else {
            session()->forget('admin_agent_pending');
        }

        return back()->with('agent_result',$result);
    }

    public function confirm(Request $request)
    {
        $action=session('admin_agent_pending');
        abort_unless(is_array($action), 400);

        $product=Product::findOrFail((int)($action['product_id'] ?? 0));

        switch ($action['type'] ?? '') {
            case 'set_price':
                $product->update(['price'=>(float)$action['price']]);
                $message='قیمت محصول با موفقیت تغییر کرد.';
                break;
            case 'set_active':
                $product->update(['is_active'=>(bool)$action['active']]);
                $message=$product->is_active ? 'محصول فعال شد.' : 'محصول غیرفعال شد.';
                break;
            case 'delete_product':
                $product->load('media');
                foreach($product->media as $media) {
                    \Storage::disk('public')->delete($media->path);
                }
                $product->delete();
                $message='محصول حذف شد.';
                break;
            default:
                $message='این عملیات قابل اجرا نیست.';
        }

        session()->forget('admin_agent_pending');
        return back()->with('success',$message);
    }

    public function cancel()
    {
        session()->forget('admin_agent_pending');
        return back()->with('success','عملیات Agent لغو شد.');
    }
}
