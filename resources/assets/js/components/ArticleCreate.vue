<template id="article-create-template">
      <div class="article-create">
            <textarea class="form-control" placeholder="What´s on your mind today?" v-model="newArticle"></textarea>
            <button @click="createArticle()" class="btn btn-primary" type="submit">Submit</button>
        </div>
</template> 
   
   
<script>
import moment from 'moment'

export default {
    name: 'ArticleCreate',
    methods: {
        /**
        * Creates a new article model as soon as the users submits the input
        */
        createArticle(){
            let article = {
                'text' : this.newArticle,
                'published' : moment().format('Y-M-D')
            }
            this.$store.dispatch('addArticle', { article });
            // clear input field
            this.newArticle = "";
        },
      },
  data () {
    return {
      newArticle : '',
    }
  }
}
</script>

<style scoped>
    .article-create-wrap textarea {
        margin-bottom:10px;
        min-height:140px;
}
</style>
