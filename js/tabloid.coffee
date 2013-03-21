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
      fig = $("""<figure><a href="/more-real/truthiness/tabloid.php?id=#{snap.name().replace(/\s/, '')}"><img src="#{snap.val().cover}"></a></figure>""")
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
    size = parseInt($p.css('font-size'))*1.5 + 'px'
    font = size + " " + $p.css('font-family')
    font

  line_count: -> $p.height()/61

  # This wrapping fails for extremely long words, but I don't care?
  wrap_text: (ctx, headline, x, y, maxWidth, lineHeight) ->
    words = headline.split(' ')
    line = ''
    for word in words
      newLine = "#{line} #{word}"
      if ctx.measureText(newLine).width > maxWidth
        ctx.fillText(line, x, y, maxWidth)
        line = word + ' '
        y += lineHeight
      else
        line = newLine
    ctx.fillText(line, x, y, maxWidth)

  draw: (headline, image=true) ->
    Upload.drawImage() if Upload.file() && image
    $context.drawImage($cover_image[0], 0, 0)
    headline ?= Tabloid.headline()
    Tabloid.wrap_text($context, headline, 360, 167, 555, 71)

  reset: ->
    $canvas.width = $canvas.width # this clears the canvas somehow
    $cover_image[0].src = "images/tabloid.png"
    @prepareCanvas()

  reset_with_uploaded_image: ->
    $('#images img').removeClass('selected')
    Tabloid.reset()
    setTimeout (-> Tabloid.draw(null)), 50

  reset_with_collection_image: (img) ->
    return unless img ?= $('#images img.selected')?[0]
    $(img).addClass('selected')
    Tabloid.reset()
    setTimeout (->
      Tabloid.draw(null)
      Upload.drawImage(img)
    ), 50

  save: ->
    Gallery.push(@headline())
    LocalStorage.push(@headline())
    Gallery.isotope()

  strip: (html) ->
    tmp = document.createElement("DIV")
    tmp.innerHTML = html
    tmp.textContent||tmp.innerText

  setHeadline: (headline) ->
    $p.html(@strip(headline).replace(/\n/, ''))
    @draw()
    $social.data('url', null)

  headline: -> $p.text()

  debug: -> $('#tabloid .generated').toggle()
  flip: ->
    Tabloid.setHeadline($p.text())
    $('#flipbook').turn('next')

window.Upload =
  uploaded_files: -> FileReader? && $("#tabloid #upload")[0].files
  collection_image: -> $("#images img.selected")[0]
  file: -> @collection_image() || @uploaded_files()[0]

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

  drawImage: (img) ->
    Tabloid.draw(null, false)
    if img
      $context.drawImage(img, 80, 454, 236, 236)
      Upload.replaceSourceImage(img)
    else
      return unless FileReader?
      @readImage().done (img) ->
        # draw `img` at coordinate 80,454 (x,y from top left), resized to 236x236
        $context.drawImage(img, 80, 454, 236, 236)
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
  d.on 'click.tabloid', '#tabloid .save .button', -> Tabloid.flip()
  d.on 'click.tabloid', '#tabloid #social a', (e) -> Share.init(e, @)
  d.on 'change.tabloid', '#tabloid input', -> Tabloid.reset_with_uploaded_image()
  d.on 'click.tabloid', '#tabloid #images img', -> Tabloid.reset_with_collection_image(@)

# this will fire once the required scripts have been loaded
$ ->
  setup() if $('canvas').length
