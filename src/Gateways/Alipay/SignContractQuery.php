<?php

/*
 * The file is part of the payment lib.
 *
 * (c) Leo <dayugog@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Payment\Gateways\Alipay;

use Payment\Contracts\IGatewayRequest;
use Payment\Exceptions\GatewayException;
use Payment\Helpers\ArrayUtil;

/**
 * 支付宝签约查询
 * User: zmm
 * DateTime: 2021/8/26 16:19
 * Class Rescind
 * @package Payment\Gateways\Alipay
 */
class SignContractQuery extends AliBaseObject implements IGatewayRequest
{
    const METHOD = 'alipay.user.agreement.query';

    /**
     * @param  array  $requestParams
     * @return mixed
     */
    protected function getBizContent(array $requestParams)
    {
        $bizContent = [
            'alipay_user_id'        => $requestParams['alipay_user_id'] ?? '' ,
            'personal_product_code' => 'GENERAL_WITHHOLDING_P' ,
            'sign_scene'            => $requestParams['sign_scene'] ?? 'INDUSTRY|CARRENTAL' ,
            'agreement_no'          => $requestParams['agreement_no'] ?? '' ,

        ];

        $bizContent = ArrayUtil::paraFilter($bizContent);

        return $bizContent;
    }

    /**
     * 获取第三方返回结果
     * @param  array  $requestParams
     * @return mixed
     * @throws GatewayException
     */
    public function request(array $requestParams)
    {
        try {
            $params = $this->buildParams(self::METHOD , $requestParams);
            $ret    = $this->get($this->gatewayUrl , $params);
            $retArr = json_decode($ret , true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new GatewayException(sprintf('format order info sync data get error, [%s]' ,
                    json_last_error_msg()) , Payment::FORMAT_DATA_ERR , ['raw' => $ret]);
            }
            $content = $retArr['alipay_user_agreement_query_response'];
            if ($content['code'] !== self::REQ_SUC) {
                throw new GatewayException(sprintf('request get failed, msg[%s], sub_msg[%s]' , $content['msg'] ,
                    $content['sub_msg']) , Payment::SIGN_ERR , $content);
            }
            $signFlag = $this->verifySign($content , $retArr['sign']);
            if (!$signFlag) {
                throw new GatewayException('check sign failed' , Payment::SIGN_ERR , $retArr);
            }
            return $content;
        } catch (GatewayException $e) {
            throw $e;
        }
    }
}