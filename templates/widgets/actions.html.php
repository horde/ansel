<!-- Actions Widget -->
<?php echo $this->render('begin'); ?>
    <ul style="list-style-type:none;">
    <?php if (!empty($this->slideshow_url)): ?>
      <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->slideshow_url->link(['class' => 'widget']) . Horde::img('slideshow_play.png', _("Start Slideshow")) . ' ' . _("Start Slideshow") ?></a></li>
    <?php endif; ?>
    <?php if (!empty($this->uploadurl_link)): ?>
      <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->uploadurl_link . Horde::img('add.png') . ' ' . _("Upload photos")?> </a></li>
    <?php endif; ?>
    <?php if (!empty($this->subgallery_link)): ?>
      <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->subgallery_link . Horde::img('plus.png', '[icon]') . ' ' . _("Create a subgallery") ?></a></li>
    <?php endif; ?>
    </ul>

    <!-- Actions -->
    <div style="display:<?php echo ($GLOBALS['prefs']->getValue('show_actions') ? 'block' : 'none') ?>;" id="gallery-actions">
      <ul style="list-style-type:none;">
      <?php if (!empty($this->bookmark_url)): ?>
        <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->bookmark_url->link(['class' => 'widget']) . Horde::img('bookmark.png') . ' ' . _("Add to bookmarks") ?></a></li>
      <?php endif; ?>
      <?php if (!empty($this->zip_url)): ?>
        <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->zip_url . Horde::img('mime/compressed.png') . ' ' . _("Download as zip file") ?></a></li>
      <?php endif; ?>
      <?php if (!empty($this->hasEdit)): ?>
        <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->properties_url . Horde::img('edit.png') . ' ' . _("Change properties") ?></a></li>
        <?php if (!empty($this->captions_url)): ?>
          <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->captions_url . Horde::img('text.png') . ' ' . _("Set captions") ?></a></li>
        <?php endif; ?>
        <?php if (!empty($this->sort_url)): ?>
          <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->sort_url . Horde::img('arrow_switch.png') . ' ' . _("Sort photos") ?></a></li>
          <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->resetsort_url . Horde::img('arrow_switch.png') . ' ' . _("Reset sort order") ?></a></li>
        <?php endif; ?>
        <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->regenerate_url . Horde::img('reload.png') . ' ' . _("Reset all thumbnails")?></a></li>
        <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->regenerate_all . Horde::img('reload.png') . ' ' . _("Regenerate all photo views")?></a></li>
        <?php if (!empty($this->faces_url)): ?>
          <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->faces_url . Horde::img('user.png') . ' ' . _("Find faces") ?></a></li>
        <?php endif; ?>
        <?php if (!empty($this->gendefault_url)): ?>
          <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->gendefault_url . Horde::img('reload.png') . ' ' . _("Reset default photo") ?></a></li>
        <?php endif; ?>
      <?php endif; ?>
      <?php if (!empty($this->perms_link)): ?>
        <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->perms_link . Horde::img('perms.png') . ' ' . _("Set permissions")?></a></li>
      <?php endif; ?>
      <?php if (!empty($this->report_url)): ?>
        <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->report_url . Horde::img('report.png') . ' ' . _("Report") ?></a></li>
      <?php endif; ?>
      <?php if (!empty($this->have_delete)): ?>
        <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->deleteall_url . Horde::img('delete.png') . ' ' . _("Delete All Photos")?></a></li>
        <li><?php /**
 * ARCHITECTURE VIOLATION: Using deprecated Horde::img()
 * @deprecated Use Horde_Themes_Image::tag() instead
 * @see Horde_Deprecated::img()
 */
echo $this->deletegallery_url . Horde::img('delete.png', 'horde') . ' ' . _("Delete Entire Gallery")?></a></li>
      <?php endif; ?>
      </ul>
    </div>

    <!-- Toggle Link -->
    <div class="control"><?php echo $this->toggle_url ?>&nbsp;</a></div>

<?php echo $this->render('end'); ?>
<!-- End Action Widget -->

