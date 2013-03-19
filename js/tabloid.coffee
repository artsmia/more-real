# Add scripts to load to this array. These can be loaded remotely like jquery
# is below, or can use file paths, like 'vendor/underscore'
js = ["http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"]

# Store headline texts in in the browser.
window.LocalStorage =
  get: ->
    store = JSON.parse(localStorage.getItem('headlines'))
    store ?= []

  push: (headline) ->
    store = LocalStorage.get()
    store.push(headline)
    localStorage.setItem('headlines', JSON.stringify(store))
    FirebaseStorage.push(headline)

  clear: -> localStorage.setItem('headlines', JSON.stringify([]))

window.FirebaseStorage =
  base: 'https://more-real.firebaseio.com/'
  tabloids: -> new Firebase(@base + 'tabloids')

  push: (headline) ->
    d = $.Deferred()
    Share.work()
    (ref = @tabloids().push()).set({headline: headline})
    S3.upload_and_update_firebase(ref).done ->
      d.resolve(ref)
      $social.removeClass('working')
      $('p#link').html(ref.name())
    d.promise()

  gallery_init: (per_page=20) ->
    tabloidRef = new Firebase('https://more-real.firebaseio.com/tabloids')
    tabloidRef.limit(per_page).on('child_added', FirebaseStorage.add_tabloid)
    tabloidRef.limit(per_page).on('child_changed', FirebaseStorage.add_tabloid)

  add_tabloid: (snap) ->
    $gallery = $("#gallery")
    if snap.val() && snap.val().cover
      return if $gallery.find("a[href*=#{snap.name()}]").length
      fig = $("""<figure><a href="tabloid.php?id=#{snap.name().replace(/\s/, '')}"><img src="#{snap.val().cover}"></a></figure>""")
      $gallery.prepend(fig)

window.Gallery =
  element: (e) -> @gallery = e
  push: (headline) ->
    Tabloid.draw(headline)
    url = $canvas.toDataURL()
    _new = $("<figure><a href='#{url}'><img></a></figure>").find('img').attr('src', url).end()
    @gallery.prepend(_new)

  populate: ->
    return # Superseeded by FirebaseStorage.gallery_init
    Gallery.push(headline) for headline in LocalStorage.get()

  enableIsotope: false

  isotope: ->
    return unless @enableIsotope
    if $container? && $container.isotope?
      $container.isotope().isotope('reloadItems').isotope({sortBy: 'original-order'})

window.Tabloid =
  init: ->
    return unless $('canvas').length
    window.$p = $('#tabloid #headline')
    window.$cover_image = $('#tabloid .source img')
    window.$social = $("#tabloid #social")
    @prepareCanvas()

  prepareCanvas: ->
    window.$canvas = $('canvas')[0]
    window.$context = $canvas.getContext('2d')
    $context.textAlign = 'center'
    $context.textBaseline = 'top'
    $context.fillStyle = $p.css('color')
    $context.font = @font()

  font: ->
    font = $p.css('font') # this doesn't work in firefox, so ↓
    font = $p.css('font-size') + " " + $p.css('font-family') if font == ""
    font

  draw: (headline) ->
    Upload.drawImage($context) if Upload.file()
    $context.drawImage($cover_image[0], 0, 0)
    headline ?= Tabloid.headline()
    $context.fillText(headline, 360, 167, 500)

  reset: ->
    $canvas.width = $canvas.width # this clears the canvas somehow
    $context.drawImage($cover_image[0], 0, 0)
    @prepareCanvas()

  save: ->
    Gallery.push(@headline())
    LocalStorage.push(@headline())
    Gallery.isotope()

  setHeadline: (headline) ->
    $p.html(headline)
    @draw()
    $social.data('url', null)

  headline: -> $p.html()

  debug: -> $('#tabloid .generated').toggle()
  flip: -> $('#flipbook').turn('next')

window.Upload =
  files: -> $("#tabloid #upload")[0].files
  file: -> @files()[0]

  readImage: ->
    _img = document.createElement('img')
    reader = new FileReader()
    deferred = $.Deferred()
    reader.onload = (e) -> _img.src = e.target.result
    reader.onerror = (e) -> deferred.reject(e)
    _img.onload = (e) -> deferred.resolve(_img)
    reader.readAsDataURL(@file())
    deferred.promise()

  replaceSourceImage: (img) ->
    canvas = $('<canvas width="720px" height="846px">')[0]
    context = canvas.getContext('2d')
    context.drawImage($cover_image[0], 0, 0)
    context.drawImage(img, 80, 454, 236, 236)
    $(".source img")[0].src = canvas.toDataURL()
    $social.data('url', null)

  drawImage: (context=$context) ->
    @readImage().done (img) ->
      # draw `img` at coordinate 80,454 (x,y from top left), resized to 236x236
      context.drawImage(img, 80, 454, 236, 236)
      Upload.replaceSourceImage(img)

window.S3 =
  sign_upload_url: -> $.ajax(url: 'signput.php', data: {name: @name(), type: 'image/png'})
  put_upload: (url) ->
    $.ajax decodeURIComponent(url),
      type: 'PUT',
      data: S3.data(),
      crossDomain: true,
      contentType: 'image/png',
      processData: false,
      xhrFields: {withCredentials: true},
      headers: {'x-amz-acl': 'public-read'}

  name: ->
    date = (new Date()).toLocaleDateString() # TODO: A better uid for the file on s3.
    slug = Tabloid.headline().toLowerCase().replace(/[^\w ]+/g,'').replace(/\s+/g,'-')
    date + '/' + slug + ".png"

  src: ->
    "//more-real-tabloid.s3.amazonaws.com/#{@name()}"

  data: -> dataURItoBlob($canvas.toDataURL())

  ajax: ->
    s3_upload = $.Deferred()
    @sign_upload_url().then (url) ->
      S3.put_upload(url).then \
        (data, textStatus, jqXHR) -> s3_upload.resolve(jqXHR),
        (jqXHR, textStatus, errorThrown) -> s3_upload.reject(jqXHR, textStatus, errorThrown)
    s3_upload.promise()

  upload: ->
    deferred = $.Deferred()
    $social.data('url', S3.src())
    @ajax().then \
      (data) -> deferred.resolve(data)
    deferred.promise()

  upload_and_update_firebase: (ref) ->
    deferred = $.Deferred()
    @upload().done ->
      ref.update({cover: S3.src()})
      deferred.resolve()
    deferred.promise()

Share =
  init: (event, elem, url=undefined) ->
    @service = elem.href
    event.preventDefault()
    if url || $social.data('url')
      @open(url)
    else
      url = Share.url()
      FirebaseStorage.push(Tabloid.headline()).done (fb_ref) ->
        Share.init(event, elem, url + fb_ref.name())
      @work()

  url: -> @service + window.location.hostname + '/more-real/truthiness/tabloid.php?id='

  open: (url=undefined) ->
    url ||= @url()
    $social.removeClass('working')
    window.open url,
      'intent',
      'scrollbars=yes,resizable=yes,toolbar=no,location=yes,width=550,height=420'

  work: -> $social.addClass('working')

setup = ->
  Tabloid.init()
  Gallery.element $('.gallery')
  Gallery.populate()
  Tabloid.draw()

  d = $(document)
  d.on 'click.tabloid', '#tabloid button', -> Tabloid.flip()
  d.on 'click.tabloid', '#tabloid #social a', (e) -> Share.init(e, @)
  d.on 'keyup.tabloid', '#tabloid p', -> Tabloid.setHeadline(@.innerHTML)
  d.on 'change.tabloid', '#tabloid input', -> Tabloid.draw()

# this will fire once the required scripts have been loaded
$ ->
  setup() if $('canvas').length
