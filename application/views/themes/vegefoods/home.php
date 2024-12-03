<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Meta tags, title, etc. -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    #home-section.hero .slider-item .container .slider-text h1,
    #home-section.hero .slider-item .container .slider-text p,
    #home-section.hero .slider-item .container .slider-text .btn-primary {
      font-family: "Trebuchet MS", sans-serif;
      /* Change the font here */
      color: #fff;
      /* Change the color here */
    }

    /* Styling for the floating WhatsApp button */
    .whatsapp-button {
      position: fixed;
      width: 60px;
      height: 60px;
      bottom: 40px;
      right: 40px;
      background-color: #25D366;
      color: #FFF;
      border-radius: 50px;
      text-align: center;
      font-size: 30px;
      box-shadow: 2px 2px 3px #999;
      z-index: 1000;
    }

    .whatsapp-button i {
      margin-top: 16px;
    }

    .whatsapp-button:hover {
      background-color: #1ebe57;
      color: white;
    }
  </style>
</head>

<body>
  <section id="home-section" class="hero">
    <div class="home-slider owl-carousel">
      <div class="slider-item" style="background-image: url(<?php echo get_theme_uri('images/bg1.jpg'); ?>);">
        <div class="overlay"></div>
        <div class="container">
          <div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">
            <div class="col-md-12 ftco-animate text-center">
              <h1 class="mb-2">Toko Berkah Abadi</h1>
              <p><a href="#products" class="btn btn-primary">Belanja Sekarang</a></p>
            </div>
          </div>
        </div>
      </div>
      <div class="slider-item" style="background-image: url(<?php echo get_theme_uri('images/bg2.jpg'); ?>);">
        <div class="overlay"></div>
        <div class="container">
          <div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">
            <div class="col-sm-12 ftco-animate text-center">
              <h1 class="mb-2">Mudah Aman Terpecaya</h1>
              <p><a href="#products" class="btn btn-primary">Belanja Sekarang</a></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="ftco-section" id="products">
    <div class="container">
      <div class="row justify-content-center mb-3 pb-3">
        <div class="col-md-12 heading-section text-center ftco-animate">
          <span class="subheading">Produk</span>
          <h2 class="mb-4"><?php echo get_store_name(); ?></h2>
          <p><?php echo get_settings('store_tagline'); ?></p>
        </div>
      </div>
      <div class="row">
        <?php if (count($products) > 0) : ?>
          <?php foreach ($products as $product) : ?>
            <div class="col-md-6 col-lg-3 ftco-animate">
              <div class="product">
                <a href="<?php echo site_url('shop/product/' . $product->id . '/' . $product->sku . '/'); ?>" class="img-prod">
                  <img class="img-fluid" src="<?php echo base_url('assets/uploads/products/' . $product->picture_name); ?>" alt="<?php echo $product->name; ?>">
                  <?php if ($product->current_discount > 0) : ?>
                    <span class="status"><?php echo count_percent_discount($product->current_discount, $product->price, 0); ?>%</span>
                  <?php endif; ?>
                  <div class="overlay"></div>
                </a>
                <div class="text py-3 pb-4 px-3 text-center">
                  <h3><a href="<?php echo site_url('shop/product/' . $product->id . '/' . $product->sku . '/'); ?>"><?php echo $product->name; ?></a></h3>
                  <div class="d-flex">
                    <div class="pricing">
                      <p class="price">
                        <?php if ($product->current_discount > 0) : ?>
                          <span class="mr-2 price-dc">Rp <?php echo format_rupiah($product->price); ?></span>
                          <span class="price-sale">Rp <?php echo format_rupiah($product->price - $product->current_discount); ?></span>
                        <?php else : ?>
                          <span class="mr-2"><span class="price-sale">Rp <?php echo format_rupiah($product->price); ?></span></span>
                        <?php endif; ?>
                      </p>
                    </div>
                  </div>
                  <div class="bottom-area d-flex px-3">
                    <div class="m-auto d-flex">
                      <a href="<?php echo site_url('shop/product/' . $product->id . '/' . $product->sku . '/'); ?>" class="buy-now d-flex justify-content-center align-items-center text-center">
                        <span><i class="ion-ios-menu"></i></span>
                      </a>
                      <a href="#" class="add-to-chart add-cart d-flex justify-content-center align-items-center mx-1" data-sku="<?php echo $product->sku; ?>" data-name="<?php echo $product->name; ?>" data-price="<?php echo ($product->current_discount > 0) ? ($product->price - $product->current_discount) : $product->price; ?>" data-id="<?php echo $product->id; ?>">
                        <span><i class="ion-ios-cart"></i></span>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else : ?>
          <div class="col-md-12 text-center">
            <p>No products available at the moment.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="ftco-section testimony-section">
    <div class="container">
      <div class="row justify-content-center mb-5 pb-3">
        <div class="col-md-7 heading-section ftco-animate text-center">
          <span class="subheading">Testimony</span>
          <h2 class="mb-4">Apa yang pelanggan kami katakan?</h2>
        </div>
      </div>
      <div class="row ftco-animate">
        <div class="col-md-12">
          <div class="carousel-testimony owl-carousel">
            <?php if (count($reviews) > 0) : ?>
              <?php foreach ($reviews as $review) : ?>
                <div class="item">
                  <div class="testimony-wrap p-4 pb-5">
                    <div class="user-img mb-5" style="background-image: url(<?php echo base_url('assets/uploads/users/' . $review->profile_picture); ?>)">
                      <span class="quote d-flex align-items-center justify-content-center">
                        <i class="icon-quote-left"></i>
                      </span>
                    </div>
                    <div class="text text-center">
                      <p class="mb-5 pl-4 line"><?php echo $review->review_text; ?></p>
                      <p class="name"><?php echo $review->name; ?></p>
                      <span class="position"><?php echo get_formatted_date($review->review_date); ?></span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else : ?>
              <div class="col-md-12 text-center">
                <p>Belum ada testimoni untuk saat ini.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Floating WhatsApp Button -->
  <a href="https://api.whatsapp.com/send?phone=6288216439361&text=Halo,%20saya%20mau%20bertanya%20tentang%20produk%20Anda" class="whatsapp-button" target="_blank">
    <i class="fab fa-whatsapp"></i>
  </a>

  <!-- Script dan closing tags -->
</body>

</html>