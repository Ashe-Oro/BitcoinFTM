<h1>Buy &amp; Sell Bitcoins</h1>

<div id="buysell">
	<ul id="buysell-bitcoin-markets">
	<?php
	foreach($markets as $mkt) {
		echo "<li id='buysell-btcmarket-".$mkt->getName()."' class='buysell-bitcoin-market'><a href='#'>".$mkt->getName()."</a></li>";
	}
	?>
	</ul>

	<div id="buysell-bitcoins">
		<div id="buysell-not-available">
			<h3>You do not have any capital at this market</h3>
		</div>
		<div id="order-type" class="order-item">
			<h3>Which type of order do you want to place?</h3>
			<div class="order-type-button active" id="order-type-market">Market Order</div>
			<div class="order-type-button" id="order-type-limit">Limit Order</div>
		</div>
		<div id="order-vol-price" class="order-item">
			<div id="order-volume" class="order-volprice-item">
				<h3>How many Bitcoins do you want to move?</h3>
				<input type="text" name="order-volume-val" id="order-volume-val" value="0.1" />
				<div id="order-capital">Available Capital: <span id="order-capital-usd"></span>, <span id="order-capital-btc"></span></div>
			</div>
			<div id="order-limit-price" class="order-volprice-item">
				<h3>What is your limit price per Bitcoin?</h3>
				$<input type="text" name="order-limit-price-val" id="order-limit-price-val" value="0" />
			</div>
			<div style="height: 1px; clear: both;"></div>
		</div>
		<div id="buysell-buttons" class="order-item">
			<div class="buysell-btn" id="buy-button">
				<div class="order-label">Buy</div>
				<div class="order-realtime">
					<div class="order-value">Ask: <span id="order-ask-value">...</span></div>
					<div class="order-limit">Limit: <span class="order-limit-value">0</span></div>
					<div class="order-commission">Com: <span class="order-commission-value">...</span></div>
					<div class="order-price">Total: <span id="order-buy-total">...</span></div>
				</div>
			</div>
			<div class="buysell-btn" id="sell-button">
				<div class="order-label">Sell</div>
				<div class="order-realtime">
					<div class="order-value">Bid: <span id="order-bid-value">...</span></div>
					<div class="order-limit">Limit: <span class="order-limit-value">0</span></div>
					<div class="order-commission">Com: <span class="order-commission-value">...</span></div>
					<div class="order-price">Total: <span id="order-sell-total">...</span></div>
				</div>
			</div>
		</div>
	</div>
</div>