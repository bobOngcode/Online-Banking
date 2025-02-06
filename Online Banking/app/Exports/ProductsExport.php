<?php

namespace App\Exports;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use DB;
use Auth;
use App\User;

class ProductsExport extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements WithCustomValueBinder, FromCollection, WithHeadings 
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }
    
    public function collection()
    {   
        $params = $this->params;
   
        $products = DB::table('products')
                      ->join('brands', 'products.brand_id', '=', 'brands.id')
                      ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
                      ->select(DB::raw('brands.name as brand'), 'products.model', DB::raw('product_categories.name as product_category'), 'products.serial', 'products.quantity')
                      ->where(function($query) use ($params){
                            
                        // if(isset($params['branch_id']))
                        // {
                        //     $query->where('products.branch_id', '=', $params['branch_id']);
                        // }

                        // if(isset($params['whse_code']))
                        // {
                        //     $query->where('products.whse_code', '=', $params['whse_code']);
                        // }
                        
                        if(isset($params['file_upload_log_id']))
                        {
                            $query->where('products.file_upload_log_id', '=', $params['file_upload_log_id']);
                        }
                        else
                        {
                        	// if $scanned_by value is 'Admin' then get all users from Administration branch else users from all branches
                            $users = User::with('branch')
                                        ->whereHas('branch', function($qry) use ($params) {
                                            $scanned_by = $params['scanned_by'];
                                            $condition = $scanned_by == 'Admin' ? '=' : '<>';
                                            $qry->where('name', $condition, 'Administration');
                                        })->pluck('id');

                            $query->where(function($qry) use ($users, $params){
                                    $user = Auth::user();
                                    $inventory_group = $params['inventory_group'];
                                    

                                    if(!$user->hasAnyRole('Administrator', 'Audit Admin', 'Inventory Admin') && $user->hasRole('Inventory Branch'))
                                    {   

                                        // branch users
                                        $users = User::with('branch')
                                                    ->whereHas('branch', function($qry) use ($user) {
                                                        $qry->where('id', $user->branch_id);
                                                    })->pluck('id');

                                        $qry->where('inventory_group', 'Admin-Branch')
                                            ->whereIn('user_id', $users);
                                    }
                                    // if user is Inventory Admin or has Role Inventory Admin
                                    else if(!$user->hasRole('Administrator') && $user->hasRole('Inventory Admin'))
                                    {   

                                        $qry->where('inventory_group', 'Admin-Branch')
                                            ->whereIn('user_id', $users);
                                        
                                    }
                                    // if user is Audit or has Role Audit Admin
                                    else if(!$user->hasRole('Administrator') && $user->hasRole('Audit Admin'))
                                    {
                                        // get products encoded by admin users; not encoded by branch users
                            
                                        // branch users
                                        $users = User::with('branch')
                                                    ->whereHas('branch', function($qry) {
                                                        $qry->where('name', 'Administration');
                                                    })->pluck('id');

                                        $qry->where('inventory_group', 'Audit-Branch')
                                            ->whereIn('user_id', $users);
                                    }
                                    else
                                    {
                                        $qry->where('inventory_group', '=', $inventory_group)
                                            ->whereIn('user_id', $users);
                                    }

                                })
                                ->where(function($qry){
                                    $qry->whereNull('file_upload_log_id')
                                        ->orWhere('file_upload_log_id', 0);

                                })
                                ->where(function($query) use ($params) {
                                    $whse_code = $params['whse_code'];
                                    if($whse_code !== 'ALL')
                                    {
                                        $query->where('whse_code', $whse_code);
                                    }
                                });
                        }

                        
                        
                      })
                      ->orderBy('brands.name', 'Asc')
                      ->orderBy('products.model', 'Asc')
                      ->orderBy('product_categories.name', 'Asc')
                      ->orderBy('products.serial', 'Asc')
                      ->get();

        return $products;
    }

    public function headings(): array
    {
        return [
            'BRAND',
            'MODEL',
            'CATEGORY',
            'SERIAL',
            'QUANTITY'
        ];
    }

}
