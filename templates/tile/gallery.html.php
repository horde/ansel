<!-- Gallery Tile -->
<div class="gallery-tile">
 <?php echo $this->contentTag('a', $this->tag('img', array('alt' => '[image]', 'src' => $this->gallery_image)), array('href' => $this->view_link)) ?>
 <div class="gallery-tile-caption"><?php echo $this->contentTag('a', $this->h($this->gallery->get('name')), array('href' => $this->view_link)) ?> (<?php echo $this->gallery_count ?>)</div>
 <div class="gallery-tile-stats">
   <?php if (!empty($this->properties_link)): ?>
     <?php echo $this->contentTag('a', _("Gallery Properties"), array('href' => $this->properties_link))?><br>
   <?php endif; ?>
   <?php echo _("Created") . ': ' . \Horde\Date\Format::formatDate($this->gallery->get('date_created'), $this->date_format) ?><br>
   <?php echo _("Modified") . ': ' . \Horde\Date\Format::formatDate($this->gallery->get('last_modified'), $this->date_format) ?><br>
   <?php if (!empty($owner_link)): ?>
     <?php echo _("Owner") . ': ' . $this->contentTag('a', $this->h($this->owner_string), array('href' => $this->owner_link)) ?><br>
   <?php endif; ?>
 </div>
</div>
<!-- End Gallery Tile -->
