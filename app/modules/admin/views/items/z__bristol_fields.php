<dt><?php Ecl_Helper_Html::formLabel('upgrades', 'Upgrades / Enhancements'); ?></dt>
<dd>
	<p class="note">Details of any enhancements or add ons that have been purchased.</p>
	<?php Ecl_Helper_Html::formTextarea('upgrades', 80, 4, $item->upgrades); ?>
	<p class="note" style="text-align: center;">(Use <a target="_blank" href="<?php echo $this->router()->makeAbsoluteUri('/support/help/view/wikitext'); ?>">wiki-text</a> to format your text.)</p>
</dd>

<dt><?php Ecl_Helper_Html::formLabel('future_upgrades', 'Potential Future Upgrades / Enhancements'); ?></dt>
<dd>
	<p class="note">Details of any enhancements or add ons that might be made available.</p>
	<?php Ecl_Helper_Html::formTextarea('future_upgrades', 80, 4, $item->future_upgrades); ?>
	<p class="note" style="text-align: center;">(Use <a target="_blank" href="<?php echo $this->router()->makeAbsoluteUri('/support/help/view/wikitext'); ?>">wiki-text</a> to format your text.)</p>
</dd>

<dt><?php Ecl_Helper_Html::formLabel('op_complexities', 'Operational complexities *'); ?></dt>
<dd>
	<p class="note">Details of whether the machine requires trained technical support to set up/operate, or any H&amp;S requirements.</p>
	<?php Ecl_Helper_Html::formTextarea('op_complexities', 80, 4, $item->op_complexities); ?>
	<p class="note" style="text-align: center;">(Use <a target="_blank" href="<?php echo $this->router()->makeAbsoluteUri('/support/help/view/wikitext'); ?>">wiki-text</a> to format your text.)</p>
</dd>



<dt><?php Ecl_Helper_Html::formLabel('maintenance', 'Maintenance'); ?></dt>
<dd>
	<p class="note">Details of maintenance requirements and costs.</p>
	<?php Ecl_Helper_Html::formTextarea('maintenance', 80, 4, $item->maintenance); ?>
	<p class="note" style="text-align: center;">(Use <a target="_blank" href="<?php echo $this->router()->makeAbsoluteUri('/support/help/view/wikitext'); ?>">wiki-text</a> to format your text.)</p>
</dd>

<dt><?php Ecl_Helper_Html::formLabel('life_expectancy', 'Expected life'); ?></dt>
<dd>
	<p class="note">Expected end date of use.</p>
	<?php Ecl_Helper_Html::formInput('life_expectancy', 50, 100, $item->life_expectancy); ?>
</dd>

<dt><?php Ecl_Helper_Html::formLabel('date_purchased', 'Date purchased *'); ?></dt>
<dd>
	<p class="note">Date purchased (Enter either the year of purchase, or the full date using yyyy-mm-dd format)</p>
	<?php Ecl_Helper_Html::formInput('date_purchased', 10, 10, $item->date_purchased); ?>
</dd>



<dt><?php Ecl_Helper_Html::formLabel('restrictions', 'Restrictions on use'); ?></dt>
<dd>
	<p class="note">Details of any reason why the machine would not be easily available e.g. already fully utilised for a specific project.</p>
	<?php Ecl_Helper_Html::formTextarea('restrictions', 80, 4, $item->restrictions); ?>
	<p class="note" style="text-align: center;">(Use <a target="_blank" href="<?php echo $this->router()->makeAbsoluteUri('/support/help/view/wikitext'); ?>">wiki-text</a> to format your text.)</p>
</dd>

<dt><?php Ecl_Helper_Html::formLabel('portability', 'Portability'); ?></dt>
<dd>
	<p class="note">Is the equipment portable?.</p>
	<?php Ecl_Helper_Html::formTextarea('portability', 80, 4, $item->portability); ?>
	<p class="note" style="text-align: center;">(Use <a target="_blank" href="<?php echo $this->router()->makeAbsoluteUri('/support/help/view/wikitext'); ?>">wiki-text</a> to format your text.)</p>
</dd>



<dl>

	<dt><?php Ecl_Helper_Html::formLabel('disposed', 'Disposed of?'); ?></dt>
	<dd>
		<?php
		$options = array('0' => '-- None --' ,
						'Sold' => 'Sold' ,
						'Scrapped' => 'Scrapped' ,
						);
		Ecl_Helper_Html::formSelect('disposed', $options, $item->disposed);
		?>
	</dd>

	<dt><?php Ecl_Helper_Html::formLabel('disposal_date', 'Disposal date'); ?></dt>
	<dd>
		<?php Ecl_Helper_Html::formInput('disposal_date', 10, 10, $item->disposal_date); ?>
	</dd>

</dl>



<dt><?php Ecl_Helper_Html::formLabel('comments', 'Comments'); ?></dt>
<dd>
	<p class="note">Any other information?</p>
	<?php Ecl_Helper_Html::formTextarea('comments', 80, 4, $item->comments); ?>
	<p class="note" style="text-align: center;">(Use <a target="_blank" href="<?php echo $this->router()->makeAbsoluteUri('/support/help/view/wikitext'); ?>">wiki-text</a> to format your text.)</p>
</dd><?php
