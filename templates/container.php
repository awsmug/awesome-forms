<?php
/**
 * Template: container.php
 *
 * Available data: $id, $form_id, $label, $sort, $elements
 *
 * @package TorroForms
 * @since 1.0.0
 */
?>
<?php if ( ! empty( $label ) ) : ?>
	<h2 class="torro-container-title"><?php echo esc_html( $label ); ?></h2>
<?php endif; ?>

<?php foreach ( $elements as $element ) : ?>
	<?php torro()->template( 'element', $element ); ?>
<?php endforeach; ?>
