<h1>Transfer Bitcoins Between Markets</h1>

<div id="transfer-volume" class="transfer-item">
  <h3>How many Bitcoins do you want to move?</h3>
  <input type="text" name="transfer-volume-val" id="transfer-volume-val" value="0.1" />
</div>

<div id="transfer-markets" class="transfer-item">
  <div class="buy-market">
    <div class="buysell-btn" id="transfer-from">
      <div class="order-label">From</div>
      <div class="order-realtime">
        <div class="order-market">
            @ <select id="transfer-select-from" class="transfer-select">
            <?php foreach($markets as $mkt) {
              echo "<option value='{$mkt->mname}'>{$mkt->mname}</option>";
            } ?>
            </select>
        </div>
        <div class="order-avail"><b><span id="transfer-from-btc">...</span></b></div>
        <div class="order-bid">Bid: <span id="transfer-from-bid">...</span></div>
        <div class="order-value">Value: <span id="transfer-from-value">...</span></div>
      </div>
    </div> 
  </div>

  <div class="sell-market">
    <div class="buysell-btn" id="transfer-to">
      <div class="order-label">To</div>
      <div class="order-realtime">
        <div class="order-market">
            @ <select id="transfer-select-to" class="transfer-select">
            <?php foreach($markets as $mkt) {
              echo "<option value='{$mkt->mname}'>{$mkt->mname}</option>";
            } ?>
            </select>
        </div>
        <div class="order-avail"><b><span id="transfer-to-btc">...</span></b></div>
        <div class="order-bid">Bid: <span id="transfer-to-bid">...</span></div>
        <div class="order-value">Value: <span id="transfer-to-value">...</span></div>
      </div>
    </div> 
  </div>
  <div style="height: 1px; clear: both;"></div>
</div>

<div class="transfer-item">
  <div class="buysell-btn" id="transfer-btn">
    <div class="order-label">Transfer</div>
    <div class="order-realtime">
      <div class="transfer-bid-spread">Spread: <span id="transfer-spread">...</span></div>
      <div class="transfer-profit">Profit: <span id="transfer-profit">...</span></div>
      <div class="transfer-click-label">Click to begin Transfer</div>
    </div>
  </div> 
</div>