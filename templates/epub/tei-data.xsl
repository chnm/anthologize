<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:html="http://www.w3.org/1999/xhtml"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  xmlns:xd="http://www.oxygenxml.com/ns/doc/xsl"
  xmlns:az="http://www.anthologize.org/ns" version="1.0">
  <xd:doc scope="stylesheet">
    <xd:desc>
      <xd:p><xd:b>Created on:</xd:b> Aug 21, 2010</xd:p>
      <xd:p><xd:b>Author:</xd:b> Patrick Rashleigh</xd:p>
      <xd:p>Standard way of accessing the anthologize TEI exchange format (an
        API of sorts)</xd:p>
      <xd:p>Include this file in your transforms and use the variables
        initialized here</xd:p>
    </xd:desc>
  </xd:doc>


  <xsl:variable name="book.title-page"
    select="/tei:TEI/tei:text/tei:front/tei:head[@type='titlePage']"/>
  <xsl:variable name="book.title-page.main-title"
    select="$book.title-page/tei:bibl/tei:title[@type='main']"/>
  <xsl:variable name="book.title"
    select="$book.title-page/tei:bibl/tei:title[@type='main']"/>
  <xsl:variable name="book.title-page.sub-title"
    select="$book.title-page/tei:bibl/tei:title[@type='sub']"/>
		
  <xsl:variable name="book.title-page.doc-author"
    select="$book.title-page/tei:bibl/tei:author[@role='projectCreator']"/>

  <!-- Dedication -->

  <xsl:variable name="book.dedication">
    <xsl:copy-of
      select="/tei:TEI/tei:text/tei:front/tei:div[@n='0']"
    />
  </xsl:variable>
  <xsl:variable name="book.dedication.text" select="/tei:TEI/tei:text/tei:front/tei:div[@n='0']/div"></xsl:variable>

  <!-- Acknowledgements-->

  <xsl:variable name="book.acknowledgements"
    select="/tei:TEI/tei:text/tei:front/tei:div[@n='1']" /> 

  <xsl:variable name="book.acknowledgements.text" select="/tei:TEI/tei:text/tei:front/tei:div[@n='1']/div"></xsl:variable>

  <xsl:variable name="book.acknowledgements.title"
    select="$book.acknowledgements/tei:head/tei:title"/>

  <!-- Publication information -->

  <xsl:variable name="book.license"
    select="/tei:TEI/tei:teiHeader/tei:fileDesc/tei:publicationStmt/tei:availability"/>

  <!-- Blog posts -->

  <xsl:variable name="blog.posts"
    select="/tei:TEI/tei:text/tei:body/tei:div[@type='part']"/>

  <!-- Output parameters -->

  <xsl:variable name="parameters.root"
    select="/tei:TEI/tei:teiHeader/tei:profileDesc/az:outputDecl/az:outputParams"/>
  <xsl:variable name="parameters.font-size"
    select="normalize-space($parameters.root/*[@name='font-size']/text())"/>
  <xsl:variable name="parameters.font-family"
    select="normalize-space($parameters.root/*[@name='font-family']/text())"/>
  <xsl:variable name="parameters.paper-type"
    select="normalize-space($parameters.root/*[@name='paper-type']/text())"/>
  <xsl:variable name="parameters.page-width"
    select="normalize-space($parameters.root/*[@name='page-width']/text())"/>
  <xsl:variable name="parameters.page-height"
    select="normalize-space($parameters.root/*[@name='page-height']/text())"/>



</xsl:stylesheet>
