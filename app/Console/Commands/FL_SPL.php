<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\AdditionalLeave;
use App\Models\LeaveCardView;
use App\Models\PersonalInformation;

class FL_SPL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yearly:fl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add yearly for fl and spl';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $check = AdditionalLeave::first();
        $current_year = date('Y');

        $ids = [
            "201700267", "001", "201400188", "202100303", "199900064", "200200097", "202300324", "199100053",
            "201700272", "20230034", "198200051", "201600254", "201500252", "202300326", "199700045", "202100300",
            "202300322", "202000299", "198600029", "199400080", "202200307", "201400180", "201400185", "200300038",
            "199100050", "200000039", "201900293", "201400178", "202400339", "201900285", "201400184", "202300335",
            "201400177", "200200059", "201400182", "202400337", "201400240", "201900280", "201700271", "199000006",
            "201400181", "201800276", "198100040", "201900282", "199200016", "201700273", "199800018", "201900292",
            "202300323", "199900085", "201400222", "202300329", "201900289", "202100301", "202400341", "201400221",
            "199200075", "199600167", "201900290", "201500253", "201900281", "201400227", "202300331", "201600257",
            "201400200", "201400224", "201400194", "202300317", "201900291", "202400340", "201400210", "201400213",
            "202300320", "199100004", "202300330", "202400338", "201600258", "201800274", "201400242", "201400243",
            "199900026", "201600256", "200300012", "201800279", "201400189", "202200312", "199800063", "201400202",
            "202000300", "202100302", "202300325", "201400225", "201400208", "2014134", "201400219", "202300319",
            "201400199", "202300332", "202100304", "202300333", "201900283", "201000076", "201400176", "201400211",
            "0006", "202200311", "0553", "202200313", "0919", "202400342", "202100306", "201400209", "201400212",
            "202300328", "201700264", "202100307", "202300314", "201800275", "199800169", "202400343", "201600260",
            "201400206", "201400191", "202000305", "201800277", "199800028", "201400207", "201900287", "200200122",
            "202300327", "199700084", "198200071", "199800124", "201400229", "201400193", "201400230", "20110004",
            "201700265", "199500095", "201400244", "200300126", "202200310", "200400141", "202300315", "201400234",
            "201400232", "202200308", "200800144", "201400231", "200400142", "200300125", "199100159", "201900294",
            "199600168", "199300165", "201800278", "201900296", "201900288", "199000152", "201900295", "200800145",
            "201400237", "201400239", "201900284", "2014000238", "1572", "1127", "0005", "0190046"
        ];

        $users = PersonalInformation::whereIn('userid', $ids)->get();

        if(!$check || $check->period != $current_year){
            foreach($users as $row){
                $item = Additionalleave::where('userid', $row->userid)->first();

                $view = LeaveCardView::where('userid', $row->userid)->first();

                if(!$view){
                    $v_add = new LeaveCardView();
                    $v_add->userid = $row->userid;
                    $v_add->period = 'As of ' . date('F j, Y');
                    $v_add->vl_bal = $row->vacation_balance;
                    $v_add->sl_bal = $row->sick_balance;
                    $v_add->save();
                }

                if($item){
                    $item_deduction = (integer) $item->FL;
                    if($item_deduction != 0){
                        $card = new LeaveCardView();
                        $card->userid = $row->userid;
                        $card->period = $item->period;
                        $card->particulars = 'UNUSED FL' .'('.$item_deduction.')';
                        $card->vl_bal = (float )$row->vacation_balance - $item_deduction;
                        $card->remarks = 3;
                        $card->save();
                        $row->vacation_balance = (float) $row->vacation_balance - $item_deduction;
                        $row->save();
                    }
                }else{
                    $item = new AdditionalLeave();
                }

                $item->userid = $row->userid;
                $item->period = $current_year;
                $item->FL = 5;
                $item->SPL = 3;
                $item->save();
            }
        }

        $this->info('Success: '.json_encode($current_year));

    }
}