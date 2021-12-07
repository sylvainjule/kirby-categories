<template>
    <k-field v-bind="$props" class="k-categories-field">
        <template slot="options">
            <k-button v-if="canAdd" :id="_uid" icon="add" @click="addCategory">
                {{ $t('add') }}
            </k-button>
        </template>

        <k-empty v-if="isEmpty" icon="tag" @click="addCategory">
            {{ empty }}
        </k-empty>
        <div v-else class="k-categories-table">
            <div class="k-categories-header">
                <div v-for="language in sortedLanguages" :key="language.code" :class="[{disabled: !isCurrent(language)}, 'language']" @click="changeLanguage(language)">
                    {{ language.name }}
                </div>
                <div class="delete-placeholder"></div>
            </div>

            <k-draggable :list="localValue" :handle="true" element="ul" class="k-categories-list" @end="onInput">
                <li v-for="(category, catIndex) in localValue" class="k-categories-item">
                    <k-sort-handle />

                    <div v-for="(language, index) in sortedLanguages" :key="language.code" :class="['language', {'has-input': isCurrent(language)}]">
                        <k-input v-if="isCurrent(language)"
                                 type="text"
                                 :id="category.id"
                                 :ref="category.id"
                                 theme="field"
                                 :value="category.text"
                                 @input="onTextInput(catIndex, $event)">
                        </k-input>

                        <div v-else class="disabled">{{ category.translations[language.code] }}</div>
                    </div>

                    <k-button icon="trash" @click="removeCategory(category.id)"/>
                </li>
            </k-draggable>

        </div>
    </k-field>
</template>

<script>
export default {
    props: {
        default: [String, Boolean],
        defaultFirst: Boolean,
        limit: Number,
        prefix: String,

        // general options
        label: String,
        disabled: Boolean,
        help: String,
        parent: String,
        empty: String,
        value: [Array],
        name: [String, Number],
        required: Boolean,
        type: String
    },
    data() {
        return {
            localValue: this.value
        }
    },
    watch: {
        value: function(value) {
            if(this.localValue !== value) {
                this.localValue = value
            }
        },
    },
    computed: {
        languages() {
            return this.$store.state.languages ? this.$store.state.languages.all : this.$languages;
        },
        sortedLanguages() {
            if(!this.defaultFirst) return this.languages

            let dflt      = this.languages.find(l => l.default == true)
            let languages = this.languages.filter(l => l.default !== true)
                languages.unshift(dflt)

            return languages
        },
        currentLanguage() {
            return this.$store.state.languages ? this.$store.state.languages.current : this.$language
        },
        currentCode() {
            return this.currentLanguage.code
        },
        exists() {
            return this.localValue && this.localValue.length
        },
        isEmpty() {
            return !this.exists || !this.localValue.length
        },
        canAdd() {
            return !this.limit || !this.localValue || !this.localValue.length || this.localValue.length < this.limit
        },
        newIndex() {
            return !this.localValue || !this.localValue.length ? 1 : this.localValue.length + 1
        },
    },
    methods: {
        isCurrent(language) {
            return language.code == this.currentCode
        },
        addCategory() {
            let newId = this.prefix + this.newIndex
            let newCategory = {
                id: newId,
                panelIndex: this.newIndex,
                text: '',
                translations: {}
            }
            this.languages.forEach(l => { newCategory.translations[l.code] = '' })

            if(this.exists) {
                this.localValue = [newCategory].concat(this.localValue)
            } else {
                this.localValue = [newCategory]
            }

            this.onInput()

            this.$nextTick(() => {
                let _newInput = this.$refs[newId]
                if(_newInput) _newInput[0].focus()
            })
        },
        removeCategory(id) {
            this.localValue = this.localValue.filter(category => category.id !== id)
            this.onInput()
        },
        onTextInput(index = false, value) {
            this.localValue[index].text = value
            this.localValue[index].translations[this.currentCode] = value
            this.onInput()
        },
        onInput() {
            this.$emit('input', this.localValue)
        },
        changeLanguage(language) {
            this.$emit("change", language);
            this.$go(this.$view.path, {
                query: {
                    language: language.code
                }
            });
        }
    }
};
</script>

<style lang="scss">
    @import '../assets/css/styles.scss'
</style>
