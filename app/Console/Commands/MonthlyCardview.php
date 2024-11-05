<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\AdditionalLeave;
use App\Models\LeaveCardView;
use App\Models\PersonalInformation;
use PDO;

class MonthlyCardview extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monthly:cardview';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monthly deduction for tardiness in leave cardview';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        function conn(){
            //            $server = '192.168.110.31';
            $server = '';
            try{
                $pdo = new PDO("mysql:host=192.168.110.31; dbname=dohdtr",'rtayong_31','rtayong_31');
               // $pdo = new PDO("mysql:host=localhost; dbname=dohdtr",'root','D0h7_1T');
                $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            }
            catch (PDOException $err) {
                echo "<h3>Can't connect to database server address $server</h3>";
                exit();
            }
            return $pdo;
        }

        function getLogs($query_req){
            $pdo = conn();

            try {
                $st = $pdo->prepare($query_req);
                $st->execute();
                $row = $st->fetchAll(PDO::FETCH_ASSOC);
            }catch(PDOException $ex){
                echo $ex->getMessage();
                exit();
            }
                return $row;
        }

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

        $currentMonth = date('F');
        $currentYear = date('Y');

        $startDate = date('F j, Y', strtotime("first day of previous month"));
        $endDate = date('F j, Y', strtotime("last day of previous month"));
        $date_from = date('Y-m-d', strtotime($startDate));
        $date_to = date('Y-m-d', strtotime($endDate));
        $displaydate = date('F', strtotime($date_from)) . " 1-" .  date('d', strtotime($date_to)) . ", ". date('Y', strtotime($date_to));
        $check = LeaveCardView::where('period', $displaydate)->first();
        $all =[];

        foreach($users as $row){
            $count_absent = 0;
            try {
                $exist = DB::table('dtr_file')->where('userid', $row->userid)->first();
                if ($exist) {

                    $query_req = "CALL Gliding_2020($row->userid,'$date_from','$date_to')";
                    $timelog = getLogs($query_req);
                    $count = 0;

                    $tardiness = 0;
                    foreach($timelog as $log){
                        $tardiness = (integer) $log['late'] + (integer) $log['undertime'] + $tardiness;

                        if (strpos($log['time'], 'empty_BUI_empty_empty|empty_BUI_empty_empty') !== false) {
                            if ($log['time'] == 'empty_BUI_empty_empty|empty_BUI_empty_empty|empty_BUI_empty_empty|empty_BUI_empty_empty') {
                                $leave_logs = DB::table('leave_logs')->where('userid', $row->userid)->where('datein','=', $log['datein'])->first();
                                if(!$leave_logs){
                                    $count_absent = $count_absent + 1;
                                }
                            }else{
                                $daily = explode('|', $log['time']);
                                if($daily[0] == 'empty_BUI_empty_empty' || $daily[3] == 'empty_BUI_empty_empty'){
                                    $dtr_file = DB::table('dtr_file')->where('userid', $row->userid)->where('datein', $log['datein'])->count();
                                    if($dtr_file != 4){
                                        $edited_logs = DB::table('edited_logs')->where('userid', $row->userid)->where('datein', $log['datein'])->count();
                                        if(($dtr_file + $edited_logs) != 4){
                                            $tardiness = $tardiness + 240;
                                        }
                                    }
                                }
                            }
                        }
                        $count++;
                    }

                    $vl_bl = ($row->vacation_balance != null)?$row->vacation_balance:0;
                    $sl_bl = ($row->sick_balance != null)?$row->sick_balance:0;

                    $view = LeaveCardView::where('userid', $row->userid)->first();

                    if(!$view){
                        $v_add = new LeaveCardView();
                        $v_add->userid = $row->userid;
                        $v_add->period = 'As of ' . date('F j, Y');
                        $v_add->vl_bal = $vl_bl;
                        $v_add->sl_bal = $sl_bl;
                        $v_add->save();
                    }

                    $card_view = new LeaveCardView();
                    $card_view->userid = $row->userid;
                    $card_view->remarks = 0;
                    if($tardiness !=0){
                        $card_view->period = $displaydate;
                        if($tardiness > 60){
                            $cal = $tardiness/60;
                            $base = floor($cal);
                            $rem = $tardiness - ($base * 60);
                            $tar_deduction = round($base * 0.125, 3) + round($rem * 0.00208, 3);

                        }else{
                            $tar_deduction = round(($tardiness * 0.00208),3);
                        }

                        if($vl_bl >= $tar_deduction){
                            $card_view->vl_bal = $vl_bl - $tar_deduction;
                            $row->vacation_balance = $vl_bl - $tar_deduction;
                        }else{
                            $card_view->sl_bal = $sl_bl -  $tar_deduction;
                            $row->sick_balance =  $sl_bl -  $tar_deduction;
                        }
                        $card_view->particulars = "UT (". $tar_deduction.")";
                        $card_view->save();
                        $row->save();
                    }else{
                        $card_view->status = 1;
                        $card_view->particulars = "No UT Deduction";
                        $card_view->save();
                    }
                    $abs_days = 30 - $count_absent;
                    $abs_earned = round(0.04167 * $abs_days, 3);

                    $card_absent = new LeaveCardview();
                    $card_absent->userid = $row->userid;
                    $card_view->remarks = 1;

                    if($count_absent != 0){
                        $card_absent->particulars = 'deduct '.$count_absent.' day(s)';
                        $card_absent->vl_abswop =  round(0.04167 * $count_absent, 3);
                        $card_absent->sl_abswop =  round(0.04167 * $count_absent, 3);
                        $card_absent->date_used = $displaydate;
                    }else{
                        $card_absent->status = 1;
                        $card_absent->particulars = "No Absences";
                    }

                    $card_absent->save();

                    $row->vacation_balance = (float)$row->vacation_balance - round(0.04167 * $count_absent, 3);
                    $row->sick_balance = (float)$row->sick_balance - round(0.04167 * $count_absent, 3);
                    $row->save();

                    $add_card = new LeaveCardView();
                    $add_card->userid =  $row->userid;
                    $add_card->period =$displaydate;

                    $vl_bl = ($row->vacation_balance != null)?$row->vacation_balance:0;
                    $sl_bl = ($row->sick_balance != null)?$row->sick_balance:0;

                    $add_card->vl_earned = 1.25;
                    $add_card->vl_bal = $vl_bl +  1.25;
                    $add_card->sl_earned =  1.25;
                    $add_card->sl_bal = $sl_bl +  1.25;
                    $add_card->save();
                    $row->vacation_balance = $vl_bl + $abs_earned;
                    $row->sick_balance = $sl_bl + $abs_earned;
                    $row->save();
                }

            }catch(Exception $e){
                $errorMessage = "Error for userid {$row->userid}: " . $e->getMessage();
                return $errorMessage;
            }
        }

        $this->info('Success'.json_encode($displaydate));

    }
}