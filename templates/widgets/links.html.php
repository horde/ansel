<?php echo $this->render('begin'); ?>
<div class="ansel-widgetlink">
  <?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->contentTag(
      'a',
      Horde::img('feed.png') . ' ' . ($this->owner ? sprintf(_("Recent photos by %s"), $this->owner) : _("Recent system photos")),
      ['href' => $this->userfeedurl ]);
?>
</div>
<div class="ansel-widgetlink">
  <?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->contentTag(
      'a',
      Horde::img('feed.png') . ' ' . sprintf(_("Recent photos in %s"), $this->h($this->galleryname)),
      ['href' => $this->galleryfeedurl]);
?>
</div>
<div class="ansel-widgetlink ansel-embedlink">
  <?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo Horde::img('embed.png') . ' ' . _("Embed on external blog") ?><br>
  <textarea readonly="readonly"><?php echo $this->embed ?></textarea>
</div>
<?php echo $this->render('end'); ?>

