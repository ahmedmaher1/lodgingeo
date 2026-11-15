<?php /** @var array $page */ ?>
</main>
<footer class="site-footer">
  <div class="wrap">
    <div class="footer-cols">
      <div>
        <h2 class="footer-h">Data</h2>
        <ul>
          <li><a href="/data/hotel-contact-data/">Contact details</a></li>
          <li><a href="/data/hotel-prices-by-booking-site/">Prices by booking site</a></li>
          <li><a href="/data/hotel-building-polygons/">Building polygons</a></li>
          <li><a href="/data/hotel-location-data/">Location &amp; neighbourhood</a></li>
          <li><a href="/data/hotel-details-and-amenities/">Details &amp; amenities</a></li>
          <li><a href="/data/hotel-history-and-changes/">History &amp; changes</a></li>
          <li><a href="/hotel-matching/">List matching</a></li>
        </ul>
      </div>
      <div>
        <h2 class="footer-h">Resources</h2>
        <ul>
          <li><a href="/hotel-database-sample/">Free sample</a></li>
          <li><a href="/data-dictionary/">Data dictionary</a></li>
          <li><a href="/coverage/">Coverage</a></li>
          <li><a href="/releases/">Release log</a></li>
          <li><a href="/guides/">Guides</a></li>
          <li><a href="/faq/">FAQ</a></li>
          <li><a href="/methodology/">Methodology</a></li>
        </ul>
      </div>
      <div>
        <h2 class="footer-h">Company</h2>
        <ul>
          <li><a href="/about/">About</a></li>
          <li><a href="/contact/">Contact</a></li>
          <li><a href="/formats-and-delivery/">Formats &amp; delivery</a></li>
          <li><a href="/privacy/">Privacy</a></li>
          <li><a href="/terms/">Terms</a></li>
          <li><?= whatsapp_link() ?></li>
        </ul>
      </div>
    </div>
    <p class="footer-line">© <?= gmdate('Y') ?> <?= e(LEGAL_ENTITY) ?> · Data release <?= e(LATEST_RELEASE) ?> · <a href="mailto:<?= e(PUBLIC_EMAIL) ?>"><?= e(PUBLIC_EMAIL) ?></a></p>
    <p class="footer-line"><a href="/methodology/#attribution">Contains information from OpenStreetMap contributors (ODbL) and Overture Maps Foundation</a></p>
  </div>
<?php if (cfg('GA4_ID')): ?>
  <div class="consent" id="consent" hidden data-ga="<?= e(cfg('GA4_ID')) ?>">
    <p>May we use Google Analytics to count visits? No other tracking is used.</p>
    <p><button type="button" class="btn" data-consent="yes">Accept</button> <button type="button" class="btn" data-consent="no">Decline</button></p>
  </div>
<?php endif; ?>
</footer>
</body>
</html>
