<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function topup(Request $request) {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);
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
        try {
            $request->validate([
                'email' => 'required|email',
                'amount' => 'required|numeric|min:1',
            ]);
            $sender = auth()->user();
            if (!$sender) {
                return response()->json(['error'=>'Sender not authenticated'],401);
            }
            $receiver = User::where('email',$request->email)->firstOrFail();
            if (!$receiver) {
                return response()->json(['error' => 'Receiver not found'], 404);
            }
            
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
        } catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()],500);
        }
    }
    public function transactions(Request $request){
       $transactions = Transaction::where('sender_id', auth()->id())
        ->orWhere('receiver_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json(['success'=>true,'Transaction'=>$transactions]); // ✅ Array مباشر
       
        //return response()->json(['success'=>true,'Transaction'=>$Transaction]);
    }
    
    public function wallet(Request $request){
           $user = auth()->user();

    

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'balance' => $user->balance
    ]);
    }
    public function getUser(){
         return response()->json(auth()->user());
    }
}
