<?php 

/**
* 
*/
class VK
{
    public $from;
    public $to;
    function __construct($from, $to, $key)
    {
        $this->from = strtotime($from.' 00:00:00');
        $this->to = strtotime($to.' 23:59:59');
        $this->key = $key;
        //$this->from = strtotime('22-07-2019 00:00:00');
        //$this->to = strtotime('22-07-2019 23:59:59');
    }

    public function getStatistic()
    {   
        $fullDialogs = $this->getFullDialogsPerDate();
        $answerTimes= $this->getAnswersTimes($fullDialogs);
        $res['averageAnswerTime'] = $this->getAverageAnswerTime($answerTimes);
        $res['moda'] = $this->getModa($answerTimes);
        $res['bigAnswerTimeDialogs'] = $this->getDialogWithBigAnswerTime($fullDialogs);
        return $res;
    }

    /**
    * Получение диалогов за конкретный период дат
    * @return array
    */
    private function getFullDialogsPerDate()
    {
        $dialogs = $this->getAllDialogs();
        $dialogs = json_decode($dialogs, true);
        $res = array();
        foreach ($dialogs['response']['items'] as $dialog) {
            $singleDialog = $this->getDialogMessages($dialog["message"]["user_id"]);
            $singleDialog = json_decode($singleDialog, true);
            $dateDialog = array();
            foreach ($singleDialog['response']['items'] as $message) {
                if ($message['date'] > $this->from && $message['date'] < $this->to) {
                    $dateDialog[] = $message;
                }
            }
            $res[] = $dateDialog;
        }
        return $res;

    }

    /**
     * Получение диалогов от ВК
     * @return json
     */
    private function getAllDialogs()
    {
        return file_get_contents('https://api.vk.com/method/messages.getDialogs?v=5.41&access_token='.$this->key.'&count=10&offset=0 ');
    }

    /**
     * Получение сообщений диалогов от ВК
     * @return json
     */
    private function getDialogMessages($userId)
    {
        return file_get_contents('https://api.vk.com/method/messages.getHistory?v=5.41&access_token='.$this->key.'&peer_id='.$userId.'&offset=0&count=10 ');
    }


    /**
     * Получение массива времени ответов администратора пользователю
     * @param array $dialogs диалоги группыс сообщениями 
     * @return array время ответов администартора
     */
    private function getAnswersTimes($dialogs)
    {   
        foreach ($dialogs as $dialog) {
            for ($i=0; $i <= count($dialog); $i++) { 
                if ($dialog[$i]['out'] == 1) {
                    $res[] = $dialog[$i+1]['date'] ? ceil(($dialog[$i]['date'] - $dialog[$i+1]['date'])/60): 0 ;
                }
            }
        }
        return $res;
    }

    /**
     * Расчет среднего времени ответа администартора
     * @param array время ответов администартора 
     * @return $float
     */
    private function getAverageAnswerTime($times)
    {   $i=0;
        $res =0;
        foreach ($times as $time) {
            if ($time) {
                $res = $res +$time;
                $i++;
            }
        }
        return $res/$i;
    }

    /**
     * Расчет самого частого времени ответа администратора
     * @param array время ответов администартора  
     * @return $int
     */
    private function getModa($times)
    {   
        //$times = array(1,2,2,2,2,2,2,2,2,2,3,4,5,5,5,5,5);
        $res=array();
        foreach($times as $value)
        {
            if(isset($res[$value]))
            {
                $res[$value]++;
            }
            else $res[$value]=1;
        }
        $max = 0;
        foreach ($res as $key => $value) {
            if ($value > $max) {
                $max = $value;
                $moda = $key;
            }
        }
        return $moda;
    }

    /**
     * Получение диалогов в которых время овтета более 15 минут
     * @param array $dialogs диалоги группыс сообщениями  
     * @return $array Диалоги в которых время ответа более 15 минут
     */
    private function getDialogWithBigAnswerTime($dialogs)
    {
        foreach ($dialogs as $dialog) {
            $flag = 0;
            for ($i=0; $i <= count($dialog); $i++) { 
                if ($dialog[$i]['out'] == 1) {
                    if ( $dialog[$i]['date'] - $dialog[$i+1]['date'] > 900) {
                        $flag = 1;
                        break;
                    }
                }
            }
            if ($flag) {
                $res[]=$dialog;
            }
            
        }
        return $res;
    }
}
?>