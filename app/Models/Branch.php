<?php

namespace App\Models;

use App\User;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class Branch extends BaseModel
{
    protected $fillable = ['name', 'branch_type', 'is_default', 'tax_id', 'user_id'];

    public static function getBranch($branchId)
    {
        return Branch::select('*')->whereIn('id', explode(',', $branchId))->get();
    }

    public static function getBranchList($columnName, $columnSortedBy, $limit, $offset)
    {
        $count = Branch::count();

        $branches = Branch::select('*', 'branches.id as branch_id', 'branches.branch_type as branch_type')
            ->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();
        $taxes = Tax::get();

        foreach ($branches as $branch) {

            if ($branch['branch_type'] == 'retail') $branch['branch_type'] = Lang::get('lang.retail');
            else $branch['branch_type'] = Lang::get('lang.restaurant');

            if ($branch['tax_id'] == 0) $branch['tax'] = Lang::get('lang.no_tax');
            else {
                foreach ($taxes as $tax) {
                    if ($branch['tax_id'] == $tax['id']) {
                        $branch['tax'] = $tax['name'];
                    }
                }
            }
            $branch->branch_manager = Lang::get('lang.no_branch_manager');;
            if ($branch->user_id != null && !empty($branch->user_id)) {
                $user = User::getUserById($branch->user_id);
                $branch->branch_manager = $user->branch_manager;
            }
        }

        return ['data' => $branches, 'count' => $count];
    }

    public static function setDefaultTax($id)
    {
        Branch::where('is_default', 1, ['tax_id' => $id]);
    }

    public static function restaurantBranch()
    {
        Branch::where('branch_type', 'restaurant');
    }
}
