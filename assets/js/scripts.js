// Reply-Link: nur EIN preventDefault + robustes Data-Attribut
$(document).on('click', '.reply-link', function (e) {
  e.preventDefault();       
  var commentId = $(this).data('id');
  replyToComment(commentId);
});

// Menü
$(document).ready(function () {
  $(".menu-toggle").on("click", function () {
    $(".nav").toggleClass("showing");
    $(".nav ul").toggleClass("showing");
  });

  // ---- Slick Slider robust initialisieren ----
  var $wrap = $(".post-wrapper");
  if ($wrap.length && typeof $.fn.slick === "function") {
    var slideCount = $wrap.find(".post").length;

    // Nur initialisieren, wenn noch nicht geschehen
    $wrap.not(".slick-initialized").slick({
      slidesToShow: Math.min(3, slideCount || 1),
      slidesToScroll: 1,
      infinite: slideCount > 3,        // nur „unendlich“, wenn >3
      autoplay: slideCount > 1,        // Autoplay nur wenn sinnvoll
      autoplaySpeed: 2500,
      arrows: true,
      prevArrow: $(".post-slider .prev"),
      nextArrow: $(".post-slider .next"),
      dots: false,
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: Math.min(3, slideCount || 1),
            slidesToScroll: 1,
            infinite: slideCount > 3,
            dots: false
          }
        },
        {
          breakpoint: 600,
          settings: {
            slidesToShow: Math.min(2, slideCount || 1),
            slidesToScroll: 1,
            infinite: slideCount > 2
          }
        },
        {
          breakpoint: 480,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: slideCount > 1
          }
        }
      ]
    });
  }
});

// CKEditor
if (window.ClassicEditor) {
  ClassicEditor
    .create(document.querySelector("#body"), {
      toolbar: ["heading", "|", "bold", "italic", "link", "bulletedList", "numberedList", "blockQuote"],
      heading: {
        options: [
          { model: "paragraph", title: "Paragraph", class: "ck-heading_paragraph" },
          { model: "heading1", view: "h1", title: "Heading 1", class: "ck-heading_heading1" },
          { model: "heading2", view: "h2", title: "Heading 2", class: "ck-heading_heading2" }
        ]
      }
    })
    .catch(console.log);
}

// Reply-Funktion (leicht gehärtet)
function replyToComment(commentId) {
  var $form = $("#comment-form");
  if (!$form.length) return;

  // (Optional) Felder zurücksetzen
  if ($form[0]) $form[0].reset();

  // parent_id setzen
  $("#parent_id").val(commentId);

  // Formular sicher anzeigen
  $form.show();

  // Smooth scroll
  var top = $form.offset().top || 0;
  if ("scrollBehavior" in document.documentElement.style) {
    window.scrollTo({ top: top, behavior: "smooth" });
  } else {
    window.scrollTo(0, top);
  }
}
