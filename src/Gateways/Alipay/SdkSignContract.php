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
 * 支付宝sdk签约构建参数
 * User: zmm
 * DateTime: 2021/8/26 16:19
 * Class Rescind
 * @package Payment\Gateways\Alipay
 */
class SdkSignContract extends AliBaseObject implements IGatewayRequest
{
    const METHOD = 'alipay.user.agreement.page.sign';

    /**
     * @param  array  $requestParams
     * @return mixed
     */
    protected function getBizContent(array $requestParams)
    {
        $bizContent = [
            'product_code'          => $requestParams['product_code'] ?? 'GENERAL_WITHHOLDING' ,
            'external_logon_id'     => $requestParams['external_logon_id'] ?? '' ,
            'personal_product_code' => 'GENERAL_WITHHOLDING_P' ,
            'sign_scene'            => $requestParams['sign_scene'] ?? 'INDUSTRY|CARRENTAL' ,
            'external_agreement_no' => $requestParams['external_agreement_no'] ?? '' ,
            'access_params'         => ['channel' => 'ALIPAYAPP'] ,
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

            return urlencode(http_build_query($params));

        } catch (GatewayException $e) {
            throw $e;
        }
    }
}