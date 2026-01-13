// $(function(){
//   var admin_ajax_url  = '<?= admin_url("admin-ajax.php", __FILE__); ?>';
//   jQuery.ajax({
//     dataType: "json",
//     url: admin_ajax_url,
//     data: {
//       'action': 'text_ajax_test',
//       'text_test': 'https://api.dmm.com/affiliate/v3/ActressSearch?api_id=FQZuBJqTpt1rfdZpJRPn&affiliate_id=jajamaru0808-990&keyword=深田えいみ&sort=-bust&hits=100&offset=1&output=json',
//       'secure': '<?= wp_create_nonce('text_test_ajax') ?>'
//     }
//   });
// });