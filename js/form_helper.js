/**
 * form_helper.js
 * Handles the behavior of the brochure request form.
 */

(function($) {

  $(document).ready(function() {
    console.log('form_helper.js initialized');

  });

  // Uses the reference element to find the brochure title (which does not have
  // a unique selector).
  function findBrochureHeader(referenceElement) {
	var referenceElement = $('#' + referenceElement);
    var parentDiv = referenceElement.parent();
	var siblingOfParent = parentDiv.next();
	var headerDescendant = siblingOfParent.find('h6');
    return headerDescendant;
  }

  // Returns the 'h6' node that contains the title and image of the requested
  // brochure.
  function findBrochureDataInDOM(referenceElement) {
    var referenceElement = $('#' + referenceElement);
	var parentHeader = referenceElement.parent();
	
	return parentHeader;
  }

  function findBrochureName(referenceElement) {
	var headerElement = findBrochureDataInDOM(referenceElement);
	var titleTextObjectArray = headerElement.find('strong');
	var titleTextStringArray = [];
	titleTextObjectArray.each(function(i) {
	  titleTextStringArray[i] = $(this).text();
	});
	var titleTextString = titleTextStringArray.join(': ');
	
    return titleTextString;
  } 

  function findBrochureImageSource(referenceElement) {
    var headerElement = findBrochureDataInDOM(referenceElement);
    var imageElement = headerElement.find('img');
    var imageSource = imageElement.attr('src');

    return imageSource;
  } 

  $(window).load(function() {
    insertRequestLinks();

    var requestLinks = $('.request-link');
    $(requestLinks).on('click', function(e) {
      console.log('in the func');
	  var modalTrigger = this.id;
      var brochureName = findBrochureName(modalTrigger);
      var brochureImage = findBrochureImageSource(modalTrigger); 

      $.ajax({
        url: br_requested_brochure.ajax_url,
        type: 'POST',
        data: {
          action: 'br_get_requested_brochure_data',
          brochure_name: brochureName,
          brochure_image: brochureImage
        },
        success: function(response) {
		  updateModal(modalTrigger, response)
	    }
      });
    });
  });

  function updateModal(modalTrigger, modalData) {
    var data = JSON.parse(modalData);
    updateBrochureName(modalTrigger, data.name);
    updateBrochureTitle(modalTrigger, data.name);
    updateBrochureImage(modalTrigger, data.image);
  }

  function updateBrochureName(modalTrigger, brochureName) {
    var correspondingName = modalTrigger.replace('request-brochure-', 
	  'requested-brochure-name-');
    var nameElement = document.getElementById(correspondingName);
	nameElement.innerHTML = brochureName;
  }

  function updateBrochureTitle(modalTrigger, brochureName) {
    var correspondingTitle = modalTrigger.replace('request-brochure-', 
	  'requested-brochure-title-');
    var titleElement = document.getElementById(correspondingTitle);
	$(titleElement).val(brochureName);
  }

  function updateBrochureImage(modalTrigger, brochureImage) {
    var correspondingImage = modalTrigger.replace('request-brochure-', 
	  'requested-brochure-image-');
    var imageElement = document.getElementById(correspondingImage);
	imageElement.style.backgroundImage = 'url("'+brochureImage+'")';
  }

  function insertRequestLinks() {
    var links = $('.request-link');
    links.each(function(i) {
      var header = findBrochureHeader(links[i].id);
      var linkToAppendTo = header.find('a').first().next();
      
      var separator = document.createTextNode(' | ');
      linkToAppendTo.after(separator);
      separator.after(links[i]);
    });
  }

})(jQuery);
