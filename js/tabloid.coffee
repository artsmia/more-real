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

  clear: -> localStorage.setItem('headlines', JSON.stringify([]))

window.Gallery =
  element: (e) -> @gallery = e
  push: (headline) ->
    Tabloid.draw(headline)
    url = $canvas.toDataURL()
    _new = $("<figure><a href='#{url}'><img></a></figure>").find('img').attr('src', url).end()
    @gallery.prepend(_new)

  populate: ->
    Gallery.push(headline) for headline in LocalStorage.get()

  enableIsotope: false

  isotope: ->
    return unless @enableIsotope
    if $container? && $container.isotope?
      $container.isotope().isotope('reloadItems').isotope({sortBy: 'original-order'})

window.Tabloid =
  init: ->
    window.$p = $('#tabloid #headline')
    window.$cover_image = $('#tabloid .source img')
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
    $context.drawImage($cover_image[0], 0, 0)
    headline ?= $("input").val()
    $context.fillText(headline, 360, 167, 500)

  save: ->
    Gallery.push(@headline())
    LocalStorage.push(@headline())
    Gallery.isotope()

  setHeadline: (headline) ->
    activate_button()
    $('p').html(headline)
    @draw()

  headline: -> $p.html()

window.activate_button = ->
  window.$button = $('#tabloid button')
  $button.off 'click'
  $button.on 'click', -> Tabloid.save()

setup = ->
  Tabloid.init()
  Tabloid.draw()
  Gallery.element $('.gallery')
  Gallery.populate()

  $p.on 'keyup', -> Tabloid.setHeadline($(@).html())
  activate_button()

# this will fire once the required scripts have been loaded
if require?
  require js, ->
    $ -> setup()
else
  $ ->
    setup()
