<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.daisy.org/z3986/2005/ncx/"
  xmlns:xd="http://www.oxygenxml.com/ns/doc/xsl"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  xmlns:html="http://www.w3.org/1999/xhtml" version="1.0">
  <xd:doc scope="stylesheet">
    <xd:desc>
      <xd:p><xd:b>Created on:</xd:b> Jul 29, 2010</xd:p>
      <xd:p><xd:b>Author:</xd:b> Patrick Rashleigh</xd:p>
      <xd:p> Stylesheet to transform Anthologize TEI intermediate format into
        ePub NXC format </xd:p>
    </xd:desc>
  </xd:doc>
  
  <xsl:variable name="main-content-filename" select="'main_content.html'"/>
  
  <xsl:template match="/">
    <ncx version="2005-1">
      <head>
        <!-- The following four metadata items are required for all NCX documents,
          including those conforming to the relaxed constraints of OPS 2.0 -->

        <!-- same as in .opf -->
        <meta name="dtb:uid" content="123456789X"/>
        <!-- 1 or higher -->
        <meta name="dtb:depth" content="1"/>
        <!-- must be 0 -->
        <meta name="dtb:totalPageCount" content="0"/>
        <!-- must be 0 -->
        <meta name="dtb:maxPageNumber" content="0"/>
      </head>
      <docTitle>
        <text>
          <xsl:value-of
            select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title"
          />
        </text>
      </docTitle>
      <navMap>
        <xsl:for-each select="/tei:TEI/tei:text/tei:body/tei:div[@type='part']">
          <navPoint id="epub-chapter-{position()}" playOrder="{position()}">
            <navLabel>
              <text>
                <xsl:value-of select="tei:head/tei:title"/>
              </text>
            </navLabel>
            <content src="{$main-content-filename}#epub-chapter-{position()}"/>
          </navPoint>
        </xsl:for-each>

      </navMap>
    </ncx>
  </xsl:template>
</xsl:stylesheet>
