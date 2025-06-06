<div id="rotate-warning" style="display:none">
  <div>
    <h2>Rotate Your Device</h2>
    <p>This site is optimized for:</p>
    <ul style="list-style: none;">
      <li>Portrait on Smartphones</li>
      <li>Landscape on Tablets</li>
    </ul>
    <p>Please rotate your device to continue.</p>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const warning = document.getElementById('rotate-warning');

  if (!warning) return;

  const isPhone = window.innerWidth <= 768;
  const isLandscape = window.matchMedia("(orientation: landscape)").matches;
  const isPortrait = window.matchMedia("(orientation: portrait)").matches;

  const orientationKey = isPhone ? 'warnedLandscapePhone' : 'warnedPortraitTablet';

  const shouldWarn = (isPhone && isLandscape) || (!isPhone && isPortrait);

  function showWarning() {
    warning.style.display = "flex";
    document.body.style.overflow = "hidden";
    sessionStorage.setItem(orientationKey, 'true');
  }

  function hideWarning() {
    warning.style.display = "none";
    document.body.style.overflow = "auto";
    sessionStorage.removeItem(orientationKey);
  }

  if (shouldWarn && !sessionStorage.getItem(orientationKey)) {
    showWarning();
  } else if (!shouldWarn) {
    hideWarning();
  }

  window.addEventListener("orientationchange", () => {
    setTimeout(() => {
      sessionStorage.removeItem(orientationKey);
      location.reload(); // so the script re-runs on new orientation
    }, 500);
  });
});
</script>



