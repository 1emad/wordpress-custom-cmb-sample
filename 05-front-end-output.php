<?php
// Extracted from Templates/page/admissions.php. Runs within the theme template.
$steps = $this->get_array( 'admissions_steps' );
$faqs  = $this->get_array( 'admissions_faqs' );
?>
<div class="grid grid--4">
            <?php foreach ( $steps as $step ) : ?>
                <?php if ( empty( $step['title'] ) ) { continue; } ?>
                <div class="card card-body animate-section" data-animate="animate__fadeInUp">
                    <span class="card-tag"><?php echo esc_html( $step['number'] ?? '' ); ?></span>
                    <h3><?php echo esc_html( $step['title'] ); ?></h3>
                    <?php if ( ! empty( $step['text'] ) ) : ?><p><?php echo esc_html( $step['text'] ); ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
</div>
<?php if ( ! empty( $faqs ) ) : ?>
        <?php foreach ( $faqs as $i => $faq ) : ?>
            <?php if ( empty( $faq['question'] ) ) { continue; } ?>
            <div class="faq-box">
                <button type="button" class="faq-q" aria-expanded="false" aria-controls="faq-answer-admissions-<?php echo esc_attr( $i ); ?>">
                    <span><?php echo esc_html( $faq['question'] ); ?></span>
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="faq-answer" id="faq-answer-admissions-<?php echo esc_attr( $i ); ?>"><?php echo wp_kses_post( $faq['answer'] ?? '' ); ?></div>
            </div>
        <?php endforeach; ?>
<?php endif; ?>
