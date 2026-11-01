<table cellspacing="0" cellpadding="0" width="100%" class="w320" style="border-collapse: collapse !important; font-family: Helvetica, Arial, sans-serif;">
	<tbody>
		<tr>
			<td class="mini-container-right" style="border-collapse: collapse; color: #777777; font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 21px; padding: 10px 14px 10px 15px; text-align: center; width: 278px;">
				<table cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse !important; font-family: Helvetica, Arial, sans-serif;">
					<tbody>
						<tr style="font-family: Helvetica, Arial, sans-serif;">
							<td class="mini-block-padding" style="border-collapse: collapse; color: #777777; font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 21px; text-align: center;">
								<table cellspacing="0" cellpadding="0" width="100%" style="border-collapse: collapse !important; font-family: Helvetica, Arial, sans-serif;">
									<tbody>
										<tr style="font-family: Helvetica, Arial, sans-serif;">
											<td class="mini-block" style="background-color: #ffffff; border: 1px solid #e5e5e5; border-collapse: collapse; border-radius: 5px; color: #777777; font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 21px; padding: 12px 15px 15px; text-align: center; width: 253px;">
                                              <div class="f-fallback">
                                              <br>
                                              <strong style="line-height: 40px;"><h1 style="line-height: 45px;"><?= __tr('Subscription payment successful') ?></h1></strong>
                                                <br>
                                                <?= __tr('Hello') ?> <?= e($customerName) ?>,<br><br>
                                                <?= __tr('Thank you for your payment. Your subscription is now active.') ?>
                                                <br><br>
                                                <strong><h2><?= __tr('Subscription details') ?></h2></strong><br>
                                                <strong><?= __tr('Plan') ?>:</strong> <?= e($planTitle) ?><br>
                                                <strong><?= __tr('Billing') ?>:</strong> <?= e($planFrequency) ?><br>
                                                <strong><?= __tr('Amount paid') ?>:</strong> <?= e($planChargesFormatted) ?><br>
                                                <strong><?= __tr('Valid until') ?>:</strong> <?= e($expiryDateFormatted) ?><br>
                                                <strong><?= __tr('Transaction reference') ?>:</strong> <?= e($txnReference) ?><br>
                                                <br>
                                            </div>
									<div style="font-family: Helvetica, Arial, sans-serif;">
										<a href="<?= $subscriptionPageUrl ?>" target="_blank" style="-webkit-text-size-adjust: none; background-color: #2BAC32;border-color:#119242; border-radius: 5px; color: #ffffff; display: inline-block; font-family: 'Cabin', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: regular; line-height: 45px; mso-hide: all; text-align: center; text-decoration: none !important; width: 200px;"><?= __tr('View My Subscription') ?></a></div>
                                            </td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
