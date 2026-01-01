<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function topup(Request $request) {
        $user = auth()->user();
        $user->balance += $request->amount;
        $user->save();

        Transaction::create([
            'receiver_id'=>$user->id,
            'amount'=>$request->amount,
            'type'=>'topup'
        ]);

        return response()->json(['balance'=>$user->balance]);
    }
    public function transfer(Request $request) {
        $sender = auth()->user();
        $receiver = User::where('email',$request->email)->firstOrFail();
        if ($sender->id === $receiver->id) {
            return response()->json(['error'=>'Invalid transfer'],400);
        }
        if ($sender->balance < $request->amount) {
            return response()->json(['error'=>'Insufficient balance'],400);
        }

        $sender->balance -= $request->amount;
        $receiver->balance += $request->amount;

        $sender->save();
        $receiver->save();

        Transaction::create([
            'sender_id'=>$sender->id,
            'receiver_id'=>$receiver->id,
            'amount'=>$request->amount,
            'type'=>'transfer'
        ]);

        return response()->json(['success'=>true]);
    }
    public function transactions(Request $request){
        $Transaction = Transaction::where('sender_id', auth()->id())
        ->orWhere('receiver_id', auth()->id())
        ->latest()
        ->get();
        return response()->json(['success'=>true,'Transaction'=>$Transaction]);
    }
    
    public function wallet(Request $request){
            return response()->json([
                'balance' => auth()->user()->balance
            ]);
    }

}
