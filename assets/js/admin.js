jQuery(document).ready(function($){
  $('.quick-variants-color-field').wpColorPicker();


  /* Live color preview */
  function updateColorPreview(){
    var val = $('#button_color').val();
    if(!val) return;
    $('#qv-color-preview').css('background', val);
    $('#qv-preview-button').css({background: val, borderColor: val});
  }
  $(document).on('change input', '#button_color', updateColorPreview);
  updateColorPreview();

  // Tab functionality
  $('.qv-tab-btn').on('click', function(){
    var tab = $(this).data('tab');

    // Update tab buttons
    $('.qv-tab-btn').removeClass('active').css({
      'color': '#6b7280'
    });
    $('.qv-tab-btn svg').css('color', '#6b7280');

    // Update active tab
    $(this).addClass('active').css({
      'color': '#4f46e5'
    });
    $(this).find('svg').css('color', '#4f46e5');

    // Update tab content
    $('.qv-tab-content').addClass('hidden');
    $('#' + tab + '-tab').removeClass('hidden');
  });

  // Shortcode generator logic
  function buildShortcode(){
    var base = '[quick_variants';
    var perPage = $('#qv-gen-per-page').val().trim();
    var cats = [];
    $('.qv-gen-cat:checked').each(function(){cats.push($(this).val());});
    if(perPage){ base += ' per_page="'+ perPage +'"'; }
    if(cats.length){ base += ' category="'+ cats.join(',') +'"'; }
    base += ']';
    $('#qv-generated-shortcode').val(base);
  }
  $(document).on('input change','\#qv-gen-per-page, .qv-gen-cat', buildShortcode);
  buildShortcode();

  $('#qv-copy-shortcode').on('click', function(){
    var $btn = $(this);
    var $field = $('#qv-generated-shortcode');
    $field.trigger('select');
    try { document.execCommand('copy'); } catch(e) {}
    var original = $btn.text();
    $btn.text($btn.data('copied-text'));
    setTimeout(function(){ $btn.text(original); }, 1500);
  });

  /* Category search filter */
  $(document).on('input', '#qv-cat-search', function(){
    var q = $(this).val().toLowerCase();
    $('.qv-cat-grid label').each(function(){
      var name = $(this).data('name');
      $(this).toggle(!q || name.indexOf(q) !== -1);
    });
  });

  /* Select all / clear */
  $('#qv-cat-select-all').on('click', function(){
    $('.qv-cat-grid .qv-gen-cat').prop('checked', true); buildShortcode();
  });
  $('#qv-cat-clear').on('click', function(){
    $('.qv-cat-grid .qv-gen-cat').prop('checked', false); buildShortcode();
  });

  // Enhanced animations and interactions
  $('.qv-tab-btn').hover(
    function(){
      if(!$(this).hasClass('active')){
        $(this).css('background', '#f9fafb');
      }
    },
    function(){
      if(!$(this).hasClass('active')){
        $(this).css('background', 'transparent');
      }
    }
  );

  // Add smooth transitions to form elements
  $('input, select, textarea').on('focus', function(){
    $(this).parent().addClass('focused');
  }).on('blur', function(){
    $(this).parent().removeClass('focused');
  });
});
