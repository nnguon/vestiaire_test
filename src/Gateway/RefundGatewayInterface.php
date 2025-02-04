<?php

namespace VestiaireCollective\Gateway;

use  VestiaireCollective\Gateway\Request\RefundRequest;
use VestiaireCollective\Gateway\Result\RefundResult;
interface RefundGatewayInterface 
{
    public function refund(RefundRequest $captureRequest): RefundResult;
}
