<h1>Bitcoin Arbitrage</h1>

<div id="arbitrage-wrapper">

  <div id="arbitrage-volume" class="arbitrage-item">
    <h3>How many Bitcoins do you want to move?</h3>
    <input type="text" name="arbitrage-volume-val" id="arbitrage-volume-val" value="0.1" />
    <div id="arbitrage-capital">Available Capital: <span id="arbitrage-capital-usd"></span> @ <span class="ask-market-name"></span>, <span id="arbitrage-capital-btc"></span> @ <span class="bid-market-name"></span></div>
    <div id="arbitrage-max">Max Transaction Volume: <span id="arbitrage-max-btc"></span> (<span id="arbitrage-max-usd"></span>)</div>
  </div>

  <div id="arbitrage-buysell" class="arbitrage-item">
    <div class="buy-market">
      <div class="buysell-btn" id="arbitrage-buy-info">
        <div class="order-label">Buy</div>
        <div class="order-realtime">
          <div class="order-market">
              @ <select id="arbitrage-select-buy" class="arbitrage-select">
              <?php foreach($markets as $mkt) {
                echo "<option value='{$mkt->mname}'>{$mkt->mname}</option>";
              } ?>
              </select>
          </div>
          <div class="order-value">Ask: <span id="arbitrage-ask-value">...</span></div>
          <div class="order-commission">Com: <span class="arbitrage-commission-value">...</span></div>
          <div class="order-price">Total: <span id="arbitrage-buy-total">...</span></div>
        </div>
      </div> 
    </div>

    <div class="sell-market">
      <div class="buysell-btn" id="arbitrage-sell-info">
          <div class="order-label">Sell</span></div>
          <div class="order-realtime">
            <div class="order-market">
              @ <select id="arbitrage-select-sell" class="arbitrage-select">
              <?php foreach($markets as $mkt) {
                echo "<option value='{$mkt->mname}'>{$mkt->mname}</option>";
              } ?>
              </select>
            </div>
            <div class="order-value">Bid: <span id="arbitrage-bid-value">...</span></div>
            <div class="order-commission">Com: <span class="arbitrage-commission-value">...</span></div>
            <div class="order-price">Total: <span id="arbitrage-sell-total">...</span></div>
          </div>
        </div>
    </div>
    <div style="height: 1px; clear: both;"></div>
  </div>

  <div class="arbitrage-item">
    <div class="buysell-btn" id="arbitrage-btn">
      <div class="order-label">Arbitrage</div>
      <div class="order-realtime">
        <div class="arbitrage-profit-label">Estimated Profit:</div>
        <div class="arbitrage-profit"><span id="arbitrage-profit-usd">...</span> for <span id="arbitrage-profit-btc">...</span></div>
        <div class"arbitrage-click-label">Click to begin Arbitrage</div>
      </div>
    </div> 
  </div>
</div>

<h3 id="return-to-the-matrix"><a href="#matrix">Return to the Arbitrage Exchange Matrix</a></h3>