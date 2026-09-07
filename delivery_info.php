<?php
require_once 'core/config.php';
require_once 'includes/header.php';
?>

<section class="hero d-flex align-items-center" style="min-height: 35vh; background: url('uploads/1779271766_9886.jpeg') center/cover no-repeat; position: relative; overflow: hidden;">
  <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(1, 42, 94, 0.85) 0%, rgba(1, 42, 94, 0.6) 60%, rgba(251, 124, 0, 0.4) 100%); z-index: 1;"></div>
  <div class="hero-grid-lines" style="z-index: 1; opacity: 0.2;"></div>
  <div class="container text-center position-relative" style="z-index: 2;">
    <div style="animation:fadeUp 0.8s ease">
      <div class="section-eyebrow mb-2" style="color: var(--accent); letter-spacing: 4px; font-weight: 800;">Fast & Reliable</div>
      <h1 class="hero-title mb-3 text-white">Delivery <span style="color: var(--accent);">Info</span></h1>
      <p class="mx-auto text-white" style="max-width: 600px; font-size: 1.1rem; opacity: 0.95;">
        Everything you need to know about our delivery methods, delivery fees, and timelines.
      </p>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container py-4">
    <div class="row g-5">
        <div class="col-lg-6">
            <h2 class="section-title mb-4" style="font-size: 1.8rem; font-family: 'Playfair Display', serif;">Delivery Methods</h2>
            
            <div class="admin-card p-4 mb-4" style="border-radius: 16px;">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-motorcycle text-accent fs-3 me-3"></i>
                    <h4 style="margin: 0; font-weight: 700; font-size: 1.2rem;">Kigali Express Delivery</h4>
                </div>
                <p class="text-muted-custom" style="font-size: 0.95rem; line-height: 1.6;">Get your items delivered within 2-4 hours anywhere in Kigali.</p>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid var(--border);">
                    <span style="font-weight: 600; font-size: 0.9rem;">Estimated Time:</span>
                    <span class="text-accent fw-bold">2 - 4 Hours</span>
                </div>
            </div>

            <div class="admin-card p-4 mb-4" style="border-radius: 16px;">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-truck text-primary fs-3 me-3"></i>
                    <h4 style="margin: 0; font-weight: 700; font-size: 1.2rem;">Upcountry Standard Delivery</h4>
                </div>
                <p class="text-muted-custom" style="font-size: 0.95rem; line-height: 1.6;">Reliable delivery to all provinces via our trusted logistics partners.</p>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid var(--border);">
                    <span style="font-weight: 600; font-size: 0.9rem;">Estimated Time:</span>
                    <span class="text-primary fw-bold">24 - 48 Hours</span>
                </div>
            </div>

            <div class="admin-card p-4" style="border-radius: 16px;">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-store text-success fs-3 me-3"></i>
                    <h4 style="margin: 0; font-weight: 700; font-size: 1.2rem;">Store Pickup (Click & Collect)</h4>
                </div>
                <p class="text-muted-custom" style="font-size: 0.95rem; line-height: 1.6;">Order online and pick it up at our physical store for free.</p>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid var(--border);">
                    <span style="font-weight: 600; font-size: 0.9rem;">Estimated Time:</span>
                    <span class="text-success fw-bold">Ready in 1 Hour</span>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div style="background: var(--bg3); border-radius: var(--radius); padding: 3rem 2rem; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.02); height: 100%;">
                <h2 class="section-title mb-4" style="font-size: 1.8rem; font-family: 'Playfair Display', serif;">Frequently Asked Questions</h2>
                
                <div class="mb-4">
                    <h5 style="font-weight: 700; font-size: 1.1rem; color: var(--primary);"><i class="fas fa-box-open text-accent me-2"></i> How can I track my order?</h5>
                    <p class="text-muted-custom mt-2" style="font-size: 0.95rem; line-height: 1.6;">Once your order is dispatched, you will receive an SMS and Email with a tracking link. You can also view real-time status in the "Track My Order" section of your account.</p>
                </div>

                <div class="mb-4">
                    <h5 style="font-weight: 700; font-size: 1.1rem; color: var(--primary);"><i class="fas fa-money-bill-wave text-accent me-2"></i> Do you offer Cash on Delivery (COD)?</h5>
                    <p class="text-muted-custom mt-2" style="font-size: 0.95rem; line-height: 1.6;">Yes! We offer Pay on Delivery for all orders within Kigali. For upcountry deliveries, advance payment via Mobile Money or Card is required.</p>
                </div>

                <div class="mb-4">
                    <h5 style="font-weight: 700; font-size: 1.1rem; color: var(--primary);"><i class="fas fa-undo-alt text-accent me-2"></i> What happens if I'm not home?</h5>
                    <p class="text-muted-custom mt-2" style="font-size: 0.95rem; line-height: 1.6;">Our delivery agent will call you before arriving. If you are unavailable, we can reschedule the delivery for the next working day or leave it with a designated person.</p>
                </div>

                <div>
                    <h5 style="font-weight: 700; font-size: 1.1rem; color: var(--primary);"><i class="fas fa-shield-alt text-accent me-2"></i> What if my item arrives damaged?</h5>
                    <p class="text-muted-custom mt-2" style="font-size: 0.95rem; line-height: 1.6;">We have a strict quality control process. However, if your item is damaged during transit, please contact our support team immediately for a free replacement.</p>
                </div>

            </div>
        </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
