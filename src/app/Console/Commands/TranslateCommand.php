<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Services\TranslateService;
use App\Http\Services\OpenAIService;

class TranslateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:gpt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $original_text = '
        // 1.保証会社加入要(初回1.5万円、月額総支払額の2%/月)、2年以内の解約は違約金1ヶ月分発生、敷地内、全面禁煙。連帯保証人要、犬・猫計2匹まで飼育可、戸建賃貸物件、TVインターホン付き。
        // 2.2階角部屋、収納たっぷり、買い物便利、閑静な住宅街、当店のお薦め物件、女性限定、2面採光、南向きで日当り良好、コンビニまで210mはうれしいね、スーパーへ600m 自転車での買物も便利、都市ガス使用。
        // 3.■１０月中旬入居可能予定■解約時費用：ハウスクリーニング（1,100円／平米）・畳（琉球風畳、カラー畳など、特殊なものは除く）表替（6,050円／枚）・襖張替（3,850円／枚）・天袋張替（2,200円／枚）・障子張替（2,750円／枚）■浄水器(浄活水器)カートリッジ交換：入居中の費用は借主様負担■ペット飼育の場合
        // ';
        $original_text = '1.保証会社加入要(初回1.5万円、月額総支払額の2%/月)、2年以内の解約は違約金1ヶ月分発生、敷地内、全面禁煙。連帯保証人要、犬・猫計2匹まで飼育可、戸建賃貸物件、TVインターホン付き。';
        $openAIService = new OpenAIService();
        $translateService = new TranslateService($openAIService);
        $translateService->translate($original_text);
    }
}
