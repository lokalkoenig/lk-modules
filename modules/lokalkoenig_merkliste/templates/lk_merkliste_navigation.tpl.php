

<ul style="list-style: none; margin: 0; padding: 0; margin-right: 20px;" class="facetapi-collapsible facetapi-facet-field-kamp-themenbereiche facetapi-collapsible">
  <?php foreach($links as $link): ?>
    <li><div class="facetapi-facet">
        <a href="<?= $link['url']; ?>" class="btn <?php if(arg(1) == $link['id']) print 'facetapi-active'; else print 'facetapi-inactive'; ?>">
          <span class="badge pull-right"><?= $link['kampagnen']; ?></span><?= $link['title']; ?>
        </a>
      </div>
    </li>
  <?php endforeach; ?>
</ul>