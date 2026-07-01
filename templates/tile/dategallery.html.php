<div class="gallery-tile">
 <?php echo $this->contentTag('a', $this->tag('img', ['src' => $this->gallery_image,  'alt' => $this->caption]), ['href' => $this->view_link]) ?>
 <div class="gallery-tile-caption"><?php echo $this->contentTag('a', $this->h($this->caption), ['href' => $this->view_link]) ?><div class="gallery-tile-count">(<?php echo $this->gallery_count ?>)</div></div>
</div>
