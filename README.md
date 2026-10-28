# zone_system

**Contrast perception by the eye**

Through the adaptation of the pupil, photopigments and neural connections, the eye is capable of handling a luminance range of well over 20 f-stops; however, within a single scene, it can only perceive a significantly smaller section at any one time. Depending on the measurement method, the literature cites a simultaneous range of approximately 10 to 14 f-stops. Brightness is perceived not linearly, but approximately logarithmically or as a power function – lateral inhibition in the retina also enhances local contrasts, so that we assess edges and tonal gradations relative to their surroundings, rather than in absolute terms.

**Film and sensors**

Black-and-white negative film has a characteristic curve comprising a base, a flat section and a shoulder, and can record over ten stops. The steepness of the middle section (gamma) can be specifically adjusted via the development time. A digital sensor, by contrast, measures linearly, introduces noise in the shadows and cuts off the highlights sharply without a shoulder. At base ISO, current sensors technically achieve around 12 to 14 stops, which is roughly comparable to the human eye.

**Paper**

A print relies on reflected light and, on glossy baryta paper, achieves a maximum density of around 2.1 to 2.3. This corresponds to a dynamic range of only around 100:1 to 200:1 – roughly seven stops – and slightly less on matt paper. Every photographic workflow must therefore translate a wide range of subject detail into a limited output range, and the real creative question is where to make compromises in this process.

**The contribution of Ansel Adams and others**

Ansel Adams and Fred Archer formulated the Zone System at the Art Center School in Los Angeles around 1939–40. It translates sensitometry into a practical scale of eleven zones ranging from 0 to X, each one stop apart, with Zone V representing mid-grey. What was revolutionary was the integration of measurement, development and visual conception: before taking the photograph, the photographer determines which zone a particular part of the subject should fall into, exposes for the shadows and controls the highlights through the development process.

The problem with the modern gradation curve The gradation curve in image-editing programmes maps input values to output values in an abstract x/y diagram, using coded values rather than perceived brightness – it is a digital readout and no longer analogue sensitometry. Shifting a point on the curve also causes uncontrolled changes to neighbouring tonal ranges via spline interpolation. It is not possible to see which parts of the image lie within which section, nor which areas are compressed and which are expanded. This meant that precisely what the zone system achieved was lost: a direct, visual correspondence between parts of the subject and tonal levels.

**LightZone**

Developed by Fabio Riccardi at Light Crafts and released in 2005, it replaced the curve with the Zone Mapper. It displays a greyscale ladder comprising 16 zones, the boundaries of which can be moved, locked in place, and compressed or stretched relative to one another. The accompanying Zone Finder displays the image broken down into zones, so that the correspondence between parts of the subject and tonal levels becomes immediately apparent again. Following Light Crafts’ closure in 2011, LightZone was released under a BSD licence at the end of 2012 and has been continued as an open-source project since version 4.0 (2013). No modern software has ever integrated the Zone Mapper.

**A browser-based tool as a re-implementation**

My new tool, ‘Zone System’, implements the concept of the Zone Mapper as a single HTML file that runs entirely via JavaScript in the browser, without the need for uploads, servers or external resources. It reads black-and-white TIFFs in 8, 16 and 32-bit formats (uncompressed, LZW, Deflate, PackBits) and divides the tonal range into 16 equally spaced zones in L*. Fixed boundaries redistribute the free zones proportionally. A monotonic spline with optional smoothing and dithering prevents steps and halos in smooth gradients. The Zone Finder and 16-bit TIFF export complete the workflow. How LightZone calculates the zones internally is not documented; the division into L* is a custom specification. LightZone’s Tool Stack and selection masks have deliberately not been replicated. 

Implemented using Claude 5.5.

Translated with DeepL.com (free version)
