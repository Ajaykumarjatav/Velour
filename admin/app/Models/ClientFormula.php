<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ClientFormula extends Model
{
    protected $fillable = [
        'client_id','staff_id','base_color','highlight_color','toner','developer',
        'olaplex','natural_level','target_level','texture','scalp_condition',
        'technique','result_notes','goal','is_current','used_at',
    ];
    protected $casts = ['is_current'=>'boolean','used_at'=>'date'];
    public function client() { return $this->belongsTo(Client::class); }
    public function staff()  { return $this->belongsTo(Staff::class); }
}
