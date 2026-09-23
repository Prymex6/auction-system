/**
 * useBlogFormatting - Centralized blog formatting utilities
 * Consolidates blog color and emoji logic used across multiple components
 */

export function useBlogFormatting() {
  const blogColorsByCategory = {
    Poradnik: {
      icon: 'book',
      color1: '#0ea5e9',
      color2: '#0284c7',
      tagBg: '#dbeafe',
      tagColor: '#0369a1',
    },
    Zdrowie: {
      icon: 'health',
      color1: '#22c55e',
      color2: '#16a34a',
      tagBg: '#dcfce7',
      tagColor: '#166534',
    },
    Strategie: {
      icon: 'strategy',
      color1: '#f97316',
      color2: '#ea580c',
      tagBg: '#fed7aa',
      tagColor: '#9a3412',
    },
  }

  const defaultBlogColor = {
    icon: 'book',
    color1: '#a855f7',
    color2: '#9333ea',
    tagBg: '#f3e8ff',
    tagColor: '#7e22ce',
  }

  /**
   * Get blog color object based on category and type
   */
  const getBlogColor = (blog, type) => {
    if (!blog || !blog.category) return defaultBlogColor[type] || '#808080'
    return blogColorsByCategory[blog.category]?.[type] || defaultBlogColor[type] || '#808080'
  }

  /**
   * Get emoji for blog category
   */
  const getBlogEmoji = (blog) => {
    if (!blog || !blog.category) return '📝'

    const emojiMap = {
      Poradnik: '📚',
      Zdrowie: '🏥',
      Strategie: '🎯',
    }

    return emojiMap[blog.category] || '📝'
  }

  /**
   * Get category label with emoji and color
   */
  const getBlogCategoryBadge = (blog) => {
    return {
      emoji: getBlogEmoji(blog),
      color: getBlogColor(blog.category),
      label: blog.category || 'Artykuł',
    }
  }

  /**
   * Format blog excerpt (truncate to specified length)
   */
  const formatBlogExcerpt = (content, maxLength = 150) => {
    if (!content) return ''
    if (content.length <= maxLength) return content
    return content.substring(0, maxLength).trim() + '...'
  }

  /**
   * Format blog title for display
   */
  const formatBlogTitle = (title, maxLength = 100) => {
    if (!title) return 'Bez tytułu'
    if (title.length <= maxLength) return title
    return title.substring(0, maxLength).trim() + '...'
  }

  /**
   * Predict reading time based on content length (average 200 words per minute)
   */
  const getReadingTime = (content) => {
    if (!content) return '1 min'
    const words = content.trim().split(/\s+/).length
    const time = Math.ceil(words / 200)
    return `${time} ${time === 1 ? 'min' : 'min'}`
  }

  return {
    blogColorsByCategory,
    getBlogColor,
    getBlogEmoji,
    getBlogCategoryBadge,
    formatBlogExcerpt,
    formatBlogTitle,
    getReadingTime,
  }
}
