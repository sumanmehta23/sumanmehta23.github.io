<?php
namespace App\MT5;
use Illuminate\Support\Facades\Log;
class MTTradeProtocol
  {
  private $m_connect; // connection to MT5 server
  /**
   * @param MTConnect $connect - connect to MT5 server
   */
  public function __construct($connect)
    {
    $this->m_connect = $connect;
    }

  /**
   * Set balance
   *
   * @param int            $login user login
   * @param MTEnDealAction $type
   * @param double         $balance
   * @param string         $comment
   * @param int            $ticket
   * @param bool           $margin_check
   *
   * @return MTRetCode
   */
  public function TradeBalance($login, $type, $balance, $comment, &$ticket = null,$margin_check=true)
    {
    //--- send request
    $data = array(MTProtocolConsts::WEB_PARAM_LOGIN   => $login,
                  MTProtocolConsts::WEB_PARAM_TYPE    => $type,
                  MTProtocolConsts::WEB_PARAM_BALANCE => $balance,
                  MTProtocolConsts::WEB_PARAM_COMMENT => $comment,
                  MTProtocolConsts::WEB_PARAM_CHECK_MARGIN => $margin_check?"1":"0",
                  );
    if(!$this->m_connect->Send(MTProtocolConsts::WEB_CMD_TRADE_BALANCE, $data))
      {
      if(MTLogger::getIsWriteLog()) MTLogger::write(MTLoggerType::ERROR, 'send trade balance failed');
      return MTRetCode::MT_RET_ERR_NETWORK;
      }
    //--- get answer
    if(($answer = $this->m_connect->Read()) == null)
      {
      if(MTLogger::getIsWriteLog()) MTLogger::write(MTLoggerType::ERROR, 'answer trade balance is empty');
      return MTRetCode::MT_RET_ERR_NETWORK;
      }
    //--- parse answer
    $trade_answer = null;
    //---
    if(($error_code = $this->Parse($answer, $trade_answer)) != MTRetCode::MT_RET_OK)
      {
      if(MTLogger::getIsWriteLog()) MTLogger::write(MTLoggerType::ERROR, 'parse trade balance failed: [' . $error_code . ']' . MTRetCode::GetError($error_code));
      return $error_code;
      }
    //---
    $ticket = $trade_answer->Ticket;
    //---
    return MTRetCode::MT_RET_OK;
    }

  /**
   * check answer from MetaTrader 5 server
   *
   * @param string         $answer - answer from server
   * @param  MTTradeAnswer $trade_answer
   *
   * @return MTRetCode
   */
  private function Parse(&$answer, &$trade_answer)
    {
    $pos = 0;
    //--- get command answer
    $command_real = $this->m_connect->GetCommand($answer, $pos);
    if($command_real != MTProtocolConsts::WEB_CMD_TRADE_BALANCE) return MTRetCode::MT_RET_ERR_DATA;
    //---
    $trade_answer = new MTTradeAnswer();
    //--- get param
    $pos_end = -1;
    while(($param = $this->m_connect->GetNextParam($answer, $pos, $pos_end)) != null)
      {
      switch($param['name'])
      {
        case MTProtocolConsts::WEB_PARAM_RETCODE:
          $trade_answer->RetCode = $param['value'];
          break;
        case MTProtocolConsts::WEB_PARAM_TICKET:
          $trade_answer->Ticket = (int)$param['value'];
          break;
      }
      }
    //--- check ret code
    if(($ret_code = MTConnect::GetRetCode($trade_answer->RetCode)) != MTRetCode::MT_RET_OK) return $ret_code;
    //---
    return MTRetCode::MT_RET_OK;
    }

    public function Trade($trade_request, &$trade_answer)
    {
        $command = MTProtocolConsts::WEB_CMD_TRADE_REQUEST;
        $answer = null;

        // Encode the trade request
        $body = $trade_request;

        // Send the trade request
        $result = $this->m_connect->Send($command, $body);
        Log::info("result: {$result}");
        if ($result != MTRetCode::MT_RET_OK) {
            Log::error("Failed to send trade request. Error: " . MTRetCode::GetError($result));
            return $result;
        }
        Log::info("TradeRequest sent successfully.");

        // Read the response
        $result = $this->m_connect->ReadAnswer($command, $answer);
        if ($result != MTRetCode::MT_RET_OK) {
            Log::error("Failed to read trade response. Error: " . MTRetCode::GetError($result));
            return $result;
        }
        Log::info("TradeRequest response read successfully.");

        // Decode the answer body
        if (!isset($answer->Answer)) {
            Log::error("Invalid trade response. 'Answer' field missing.");
            return MTRetCode::MT_RET_ERR_DATA;
        }

        $trade_answer = MTJson::Decode($answer->Answer);
        Log::info("TradeRequest answer decoded: " . json_encode($trade_answer));

        Log::info("TradeRequest completed successfully.");
        return MTRetCode::MT_RET_OK;
    }


  }

/**
 * get trade answer
 */
class MTTradeAnswer
  {
  public $RetCode = '-1';
  public $Ticket = 0;
  }
