<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.idpf.org/2007/opf"
  xmlns:xd="http://www.oxygenxml.com/ns/doc/xsl"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  xmlns:html="http://www.w3.org/1999/xhtml" version="1.0">
  <xd:doc scope="stylesheet">
    <xd:desc>
      <xd:p><xd:b>Created on:</xd:b> Jul 29, 2010</xd:p>
      <xd:p><xd:b>Author:</xd:b> Patrick Rashleigh</xd:p>
      <xd:p>Consumes OWOT Anthologizer TEI intermediate format to produce ePub
        OPF format</xd:p>
    </xd:desc>
  </xd:doc>

  <xsl:variable name="main-content-filename" select="'main_content.html'"/>
  <xsl:variable name="book-id" select="'bookid'"/>
  <!--<xsl:variable name="images-directory" select="'OEBPS/images'"/>-->
  <xsl:variable name="images-directory" select="''"/>

  <xsl:template match="/">
    <package version="2.0" unique-identifier="{$book-id}">
      <metadata xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:opf="http://www.idpf.org/2007/opf">
        <dc:title>
          <xsl:value-of
            select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title"
          />
        </dc:title>
        <dc:creator>Anthologize</dc:creator>
        <!-- How to change the language? This should be a user option -->
        <dc:language>en-US</dc:language>
        <dc:rights/>
        <!--<dc:publisher>Jedisaber.com</dc:publisher>-->
        <dc:identifier id="{$book-id}">
          <xsl:value-of
            select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl/tei:ident"
          />
        </dc:identifier>
      </metadata>
      <!--  
        Each item in the manifest describes a document, an image file, a style sheet, 
        or other component that is considered part of the publication. 
        
        The URIs in href attributes of item elements in the manifest must not use fragment identifiers,
        so even if you have several chapters in one xhtml file, just list that xhtml file once.
        A single resource (href) must not be listed in the manifest more than once.
        
        Dynamically generated: list of images
      -->
      <manifest>

        <!-- A toc entry is required (and referenced in the spine/@toc attribute below) -->

        <item id="ncx" href="toc.ncx" media-type="application/x-dtbncx+xml"/>

        <!--
        <item id="style" href="stylesheet.css" media-type="text/css"/>
        <item id="pagetemplate" href="page-template.xpgt"
          media-type="application/vnd.adobe-page-template+xml"/>
        <item id="titlepage" href="title_page.xhtml"
          media-type="application/xhtml+xml"/>
        <item id="copyright" href="copyright.xhtml"
          media-type="application/xhtml+xml"/> 
        -->

        <!-- As there is only one content file with a fixed name, this can be hard-coded -->

        <item id="main_content" href="{$main-content-filename}"
          media-type="application/xhtml+xml"/>

        <!--
        <item id="legal" href="legal.xhtml" media-type="application/xhtml+xml"/>
        <item id="imgl" href="images/epublogo.png" media-type="image/png"/>
        -->

        <!-- Image references  -->

        <xsl:for-each select="/tei:TEI/tei:text//img/@src">

          <xsl:variable name="image-filename">
            <xsl:call-template name="strip-url-of-directories">
              <xsl:with-param name="url" select="."/>
            </xsl:call-template>
          </xsl:variable>

          <item id="image_{position()}" href="{$image-filename}">
            <!--href="{$images-directory}/{$image-filename}">-->
            <xsl:attribute name="media-type">
              <xsl:choose>
                <xsl:when test="contains(., '.png')">
                  <xsl:text>image/png</xsl:text>
                </xsl:when>
                <xsl:when test="contains(., '.gif')">
                  <xsl:text>image/gif</xsl:text>
                </xsl:when>
                <xsl:when test="contains(., '.jpg') or contains(., '.jpeg')">
                  <xsl:text>image/jpeg</xsl:text>
                </xsl:when>
              </xsl:choose>
            </xsl:attribute>
          </item>
        </xsl:for-each>

      </manifest>

      <!--
        The spine determines the reading order of the xhtml files.

        Each spine itemref references an OPS Content Document designated in the manifest. 
        The order of the itemref elements organizes the associated OPS Content Documents into 
        the linear reading order of the publication.
        
        The spine element must include the toc attribute, whose value is the 
        id attribute value of the required NCX document declared in manifest.
        This NCX document is what determines the navigation (as a TOC)
        
        As this list does not include image files, and the xhtml files are
        fixed (for now, there is only one, and the filename is known), then
        we can hard-code this in.
      -->

      <spine toc="ncx">
        <!--<itemref idref="titlepage"/>-->
        <!--<itemref idref="copyright"/>-->
        <!--<itemref idref="chapter01"/>-->
        <itemref idref="main_content"/>
      </spine>
    </package>
  </xsl:template>


  <xsl:template name="strip-url-of-directories">
    <xsl:param name="url"/>
    <xsl:choose>
      <xsl:when test="contains($url,'/')">
        <xsl:call-template name="strip-url-of-directories">
          <xsl:with-param name="url" select="substring-after($url,'/')"/>
        </xsl:call-template>
        <!--<xsl:value-of select="substring-after(.,'/')"/>|
          <xsl:value-of select="substring-after(substring-after(.,'/'), '/')"/>-->
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$url"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
