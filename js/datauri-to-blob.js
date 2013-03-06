// http://stackoverflow.com/a/14930686
function dataURItoBlob(dataURI) {
  var byteString, mimestring

  if(dataURI.split(',')[0].indexOf('base64') !== -1 ) {
      byteString = atob(dataURI.split(',')[1])
  } else {
      byteString = decodeURI(dataURI.split(',')[1])
  }

  mimestring = dataURI.split(',')[0].split(':')[1].split(';')[0]

  var content = new Array();
  for (var i = 0; i < byteString.length; i++) {
      content[i] = byteString.charCodeAt(i)
  }

  return new Blob([new Uint8Array(content)], {type: mimestring});
}
